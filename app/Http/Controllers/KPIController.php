<?php

namespace App\Http\Controllers;

use DateInterval;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\TokenService;
use App\Models\EmployeeTrackedTime;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class KPIController extends Controller
{

    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        return view('dashboard.kpi.index');
    }


    public function async(Request $request)
    {
        // Ambil semua employee
        $employees = Employee::all();

        // Ambil UUID dan email saja, dikirim sebagai parameter ke helper
        $uuidList = $employees->pluck('id_jibble')->filter()->toArray();
        $gidUsersAsanaList = $employees->pluck('gid_asana')->filter()->map(fn($e) => strtolower($e))->toArray();

        $from = $request->query('start_date') ?? now()->subMonth()->startOfDay()->toDateString();
        $to = $request->query('end_date') ?? now()->endOfDay()->toDateString();

        // Inject tanggal ke Request (biar konsisten di semua helper)
        $request->merge([
            'start_date' => $from,
            'end_date' => $to,
        ]);

        // Panggil data eksternal dengan list UUID dan email
        $timeTrackingData = $this->getTimeByUUIDJibble($request, $uuidList);

        $storyPointsData = $this->getStoryPointsByGidAsanaOptimized($request, $gidUsersAsanaList);

        // Mapping akhir
        $data = $employees->map(function ($employee) use ($timeTrackingData, $storyPointsData) {
            $jibbleId = $employee->id_jibble;
            $asanaId = strtolower($employee->gid_asana);

            $timeTracking = $jibbleId && isset($timeTrackingData[$jibbleId])
                ? ($timeTrackingData[$jibbleId]['formatted'] ?? '0 hours')
                : '0 hours';

            $timeTrackingMinutes = $jibbleId && isset($timeTrackingData[$jibbleId])
                ? ($timeTrackingData[$jibbleId]['minutes'] ?? 0)
                : 0;

            $storyPoints = $storyPointsData[$asanaId] ?? 0;

            $workingHourTarget = $employee->working_hour_target ?: 1; // Default 1 untuk hindari division by zero
            $storyPointTarget = $employee->story_point_target ?: 1;

            $duration = $timeTrackingMinutes > 0 ? $timeTrackingMinutes / $workingHourTarget : 0;
            $story = $storyPoints > 0 ? $storyPoints / $storyPointTarget : 0;

            $productivity = ($timeTrackingMinutes > 0 && $storyPointTarget > 0)
                ? ($storyPoints * $workingHourTarget) / ($timeTrackingMinutes * $storyPointTarget)
                : 0;

            return [
                'name' => $employee->nama_lengkap,
                'time_tracking' => $timeTracking,
                'story_points' => $storyPoints,
                'working_hour_target' => $workingHourTarget,
                'story_point_target' => $storyPointTarget,
                'duration' => round($duration, 2),
                'story' => round($story, 2),
                'productivity' => round($productivity, 2),
            ];
        });


        $response = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'data' => $data,
        ];

        return $response;
    }


    public function getStoryPointsByGidAsanaOptimized(Request $request, array $gidAsanaList)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $customFieldGid = config('asana.gid_story_point');
        $asanaToken = config('asana.bearer_token');

        $baseUri = 'https://app.asana.com/api/1.0/';
        $userPoints = [];

        // Ambil GID project dari tabel projects
        $projectGids = Project::pluck('asana_link')
            ->filter()
            ->map(function ($link) {
                // Ambil bagian ke-4 dari URL: https://app.asana.com/0/{project_gid}/{task_gid}
                preg_match('/asana\.com\/0\/(\d+)\//', $link, $matches);
                return $matches[1] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        foreach ($projectGids as $projectGid) {
            $nextPage = null;

            do {
                $tasksUrl = $nextPage ?? $baseUri . "projects/{$projectGid}/tasks";
                $tasksResponse = Http::withToken($asanaToken)->get($tasksUrl, [
                    'opt_fields' => 'assignee.gid,custom_fields.gid,custom_fields.number_value,completed,completed_at',
                    'assignee.gid' => implode(',', $gidAsanaList),
                ]);

                if ($tasksResponse->failed()) break;

                $tasks = $tasksResponse->json()['data'] ?? [];
                $nextPage = $tasksResponse->json()['next_page']['uri'] ?? null;

                foreach ($tasks as $task) {
                    if (!($task['completed'] ?? false)) continue;

                    $completedAt = $task['completed_at'] ?? null;
                    if (!$completedAt) continue;

                    $completedDate = Carbon::parse($completedAt)->toDateString();
                    if ($startDate && $completedDate < $startDate) continue;
                    if ($endDate && $completedDate > $endDate) continue;

                    $assignee = $task['assignee'] ?? null;
                    $customFields = $task['custom_fields'] ?? [];

                    $storyPointValue = 0;
                    foreach ($customFields as $field) {
                        if (($field['gid'] ?? null) === $customFieldGid && isset($field['number_value'])) {
                            $storyPointValue = $field['number_value'];
                            break;
                        }
                    }

                    if ($assignee && isset($assignee['gid']) && in_array($assignee['gid'], $gidAsanaList) && $storyPointValue > 0) {
                        $gidAsana = $assignee['gid'];
                        $userPoints[$gidAsana] = ($userPoints[$gidAsana] ?? 0) + $storyPointValue;
                    }
                }
            } while ($nextPage);
        }

        return $userPoints;
    }




    private function getTimeByUUIDJibble(Request $request, array $uuidList)
    {
        if (empty($uuidList)) return [];

        $from = $request->get('start_date');
        $to = $request->get('end_date');
        if (!$from || !$to) return [];

        $accessToken = $this->tokenService->getAccessToken();

        $response = Http::withToken($accessToken)->get('https://time-attendance.prod.jibble.io/v1/TrackedTimeReport', [
            'from' => $from,
            'to' => $to,
            'groupBy' => 'Member',
            'subGroupBy' => 'Project',
            '$expand' => 'Subject,Items($expand=Subject)',
            'personIds' => implode(',', $uuidList),
        ]);

        $data = $response->json()['value'] ?? [];

        return collect($data)->mapWithKeys(function ($item) {
            $personId = $item['subject']['id'] ?? null;
            $isoDuration = $item['trackedTime'] ?? null;

            if (!$personId || !$isoDuration) return [];

            $duration = [
                'minutes' => $this->convertIsoDurationToMinutes($isoDuration),
                'formatted' => $this->convertIsoDurationToHoursMinutes($isoDuration),
            ];

            return [$personId => $duration];
        })->toArray();
    }

    public function getStoryPointsByGidAsana(Request $request, array $gidAsanaList)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $customFieldGid = config('asana.gid_story_point');
        $asanaToken = config('asana.bearer_token');

        $baseUri = 'https://app.asana.com/api/1.0/';
        $projectsResponse = Http::withToken($asanaToken)->get($baseUri . 'projects');

        if ($projectsResponse->failed()) return [];

        $projects = $projectsResponse->json()['data'] ?? [];
        $userPoints = [];

        foreach ($projects as $project) {
            $nextPage = null;

            do {
                $tasksUrl = $nextPage ?? $baseUri . "projects/{$project['gid']}/tasks";
                $tasksResponse = Http::withToken($asanaToken)->get($tasksUrl, [
                    'opt_fields' => 'assignee.gid,custom_fields.gid,custom_fields.number_value,completed,completed_at'
                ]);

                if ($tasksResponse->failed()) break;

                $tasks = $tasksResponse->json()['data'] ?? [];
                $nextPage = $tasksResponse->json()['next_page']['uri'] ?? null;

                foreach ($tasks as $task) {
                    if (!($task['completed'] ?? false)) continue;

                    $completedAt = $task['completed_at'] ?? null;
                    if (!$completedAt) continue;

                    $completedDate = Carbon::parse($completedAt)->toDateString();
                    if ($startDate && $completedDate < $startDate) continue;
                    if ($endDate && $completedDate > $endDate) continue;

                    $assignee = $task['assignee'] ?? null;
                    $customFields = $task['custom_fields'] ?? [];

                    $storyPointValue = 0;
                    foreach ($customFields as $field) {
                        if (($field['gid'] ?? null) === $customFieldGid && isset($field['number_value'])) {
                            $storyPointValue = $field['number_value'];
                            break;
                        }
                    }

                    if ($assignee && isset($assignee['gid']) && $storyPointValue > 0) {
                        $gidAsana = $assignee['gid'];

                        // Periksa apakah GID Asana ini ada di dalam daftar yang diterima
                        if (in_array($gidAsana, $gidAsanaList)) {
                            $userPoints[$gidAsana] = ($userPoints[$gidAsana] ?? 0) + $storyPointValue;
                        }
                    }
                }
            } while ($nextPage);
        }

        return $userPoints;
    }


    public function getStoryPointsByEmail(Request $request, array $emailList)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $customFieldGid = config('asana.gid_story_point');
        $asanaToken = config('asana.bearer_token');

        $baseUri = 'https://app.asana.com/api/1.0/';
        $projectsResponse = Http::withToken($asanaToken)->get($baseUri . 'projects');

        if ($projectsResponse->failed()) return [];

        $projects = $projectsResponse->json()['data'] ?? [];
        $userPoints = [];

        foreach ($projects as $project) {
            $nextPage = null;

            do {
                $tasksUrl = $nextPage ?? $baseUri . "projects/{$project['gid']}/tasks";
                $tasksResponse = Http::withToken($asanaToken)->get($tasksUrl, [
                    'opt_fields' => 'assignee.email,custom_fields.gid,custom_fields.number_value,completed,completed_at'
                ]);

                if ($tasksResponse->failed()) break;

                $tasks = $tasksResponse->json()['data'] ?? [];
                $nextPage = $tasksResponse->json()['next_page']['uri'] ?? null;

                foreach ($tasks as $task) {
                    if (!($task['completed'] ?? false)) continue;

                    $completedAt = $task['completed_at'] ?? null;
                    if (!$completedAt) continue;

                    $completedDate = Carbon::parse($completedAt)->toDateString();
                    if ($startDate && $completedDate < $startDate) continue;
                    if ($endDate && $completedDate > $endDate) continue;

                    $assignee = $task['assignee'] ?? null;
                    $customFields = $task['custom_fields'] ?? [];

                    $storyPointValue = 0;
                    foreach ($customFields as $field) {
                        if (($field['gid'] ?? null) === $customFieldGid && isset($field['number_value'])) {
                            $storyPointValue = $field['number_value'];
                            break;
                        }
                    }

                    if ($assignee && isset($assignee['email']) && $storyPointValue > 0) {
                        $email = strtolower($assignee['email']);
                        if (!in_array($email, $emailList)) continue;

                        $userPoints[$email] = ($userPoints[$email] ?? 0) + $storyPointValue;
                    }
                }
            } while ($nextPage);
        }

        return $userPoints;
    }




    private function getEmployeeIdFromTrackedData($data)
    {
        $subject = $data['subject'] ?? [];
        $jibbleId = $subject['id'] ?? null; // biasanya ID ada di sini

        if (!$jibbleId) return null;

        $employee = Employee::where('id_jibble', $jibbleId)->first();
        return $employee ? $employee->id : null;
    }


    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Sync time tracked data with local system
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /*******  f3bd8f84-010a-4394-a883-a59d1a3b7c61  *******/
    public function syncTimeWithLocal(Request $request)
    {
        try {
            // Ambil semua ID Jibble dari tabel employee
            $personIds = Employee::whereNotNull('id_jibble')->pluck('id_jibble')->toArray();

            // Ambil rentang waktu (misalnya, bulan terakhir)
            $startDate = Carbon::now()->subMonth(3)->startOfMonth()->toDateString();
            $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            $accessToken = $this->tokenService->getAccessToken();
            $url = 'https://time-attendance.prod.jibble.io/v1/TrackedTimeReport';

            // Kirim permintaan API untuk mengambil data
            $response = Http::withToken($accessToken)->get($url, [
                'from' => $startDate,
                'to' => $endDate,
                'groupBy' => 'Member',
                'subGroupBy' => 'Activity',
                '$expand' => 'Subject,Items($expand=Subject)',
                'personIds' => implode(',', $personIds),
            ]);

            // Pastikan respon berhasil
            if ($response->failed()) {
                throw new \Exception('Failed to fetch tracked time data');
            }

            // Ambil data dari response
            $trackedData = $response->json()['value'] ?? [];


            // Sinkronkan data dengan tabel Employee
            foreach ($trackedData as $data) {
                try {
                    // Ambil personId dari data
                    $personId = $data['subject']['id'] ?? null;

                    // Pastikan personId ada dan cocok dengan ID Jibble yang ada di Employee
                    if (!$personId || !in_array($personId, $personIds)) {
                        continue; // Skip jika personId tidak ada atau tidak cocok
                    }

                    // Ambil employee_id dari tabel Employee berdasarkan id_jibble
                    $employee = Employee::where('id_jibble', $personId)->first();

                    if ($employee) {
                        // Cek jika data waktu sudah ada
                        $existingRecord = EmployeeTrackedTime::where('employee_id', $employee->id)
                            ->where('tracked_time_id', $data['id'])
                            ->first();

                        // Jika data sudah ada, update, jika belum ada, insert baru
                        if ($existingRecord) {
                            // Update data yang sudah ada
                            $existingRecord->update([
                                'tracked_time' => $data['trackedTime'],
                                'tracked_date' => Carbon::now()->toDateString(),
                            ]);
                        } else {

                            // Insert data baru
                            EmployeeTrackedTime::create([
                                'employee_id' => $employee->id,
                                'tracked_time_id' => $data['id'],
                                'tracked_time' => $data['trackedTime'],
                                'tracked_date' => Carbon::now()->toDateString(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {

                    // Jika ada error pada proses sinkronisasi data per task, log errornya dan lanjutkan
                    \Log::error('Error syncing tracked time data for personId ' . ($data['subject']['personId'] ?? 'unknown') . ': ' . $e->getMessage());
                }
            }

            return response()->json(['message' => 'Successfully synchronized Jibble time data with local system']);
        } catch (\Exception $e) {


            // Jika ada error secara keseluruhan pada sinkronisasi, log dan kembalikan error
            \Log::error('Error during sync process: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to sync time data with local system.'], 500);
        }
    }





    public function syncJibbleUsers()
    {
        try {
            // 1. Ambil data dari Jibble
            $url = config('jibble.base_uri') . 'People';
            $accessToken = $this->tokenService->getAccessToken();

            $respJibble = Http::withToken($accessToken)->get($url, [
                '$select' => 'id,email,fullName',
                '$filter' => "role eq 'Member'",
            ]);

            if ($respJibble->failed()) {
                return response()->json(['message' => 'Failed to fetch Jibble data'], 500);
            }

            $jibbleUsers = collect($respJibble->json('value', []));

            // 2. Ambil semua user Asana (satu request)
            $asanaToken = config('asana.bearer_token');
            $respAsana = Http::withToken($asanaToken)
                ->get('https://app.asana.com/api/1.0/users?workspace=' . config('asana.workspace_gid'));

            if ($respAsana->failed()) {
                return response()->json(['message' => 'Failed to fetch Asana users'], 500);
            }

            $asanaUsers = collect($respAsana->json('data', []));
            $employees = Employee::select('id', 'email_asana', 'id_jibble', 'gid_asana')->get();
            $syncedJibble = 0;
            $syncedAsanaGid = 0;

            // Sinkronkan dengan Jibble
            foreach ($employees as $emp) {
                // Sinkronkan Jibble
                $matchJ = $jibbleUsers->firstWhere('email', $emp->email_asana);
                if ($matchJ && $matchJ['id'] && $emp->id_jibble !== $matchJ['id']) {
                    $emp->id_jibble = $matchJ['id'];
                    $syncedJibble++;
                    if ($emp->isDirty(['id_jibble'])) {
                        $emp->save();
                    }
                }
            }

            // Sinkronkan dengan Asana
            foreach ($asanaUsers as $asanaUser) {
                // Ambil detail pengguna Asana untuk mendapatkan email
                $respUserDetail = Http::withToken($asanaToken)
                    ->get("https://app.asana.com/api/1.0/users/{$asanaUser['gid']}");

                if ($respUserDetail->failed()) {
                    continue;  // Jika gagal mengambil detail, lanjutkan ke pengguna berikutnya
                }

                $userDetail = $respUserDetail->json();
                $emailAsana = $userDetail['data']['email'] ?? null;

                if ($emailAsana) {
                    // Sinkronkan email Asana dengan employee
                    $employee = $employees->firstWhere('email_asana', $emailAsana);
                    if ($employee && $employee->gid_asana !== $asanaUser['gid']) {
                        $employee->gid_asana = $asanaUser['gid'];
                        $employee->save();
                        $syncedAsanaGid++;
                    }
                }
            }

            return response()->json([
                'message' => 'Sync successful',
                'jibble_synced' => $syncedJibble,
                'asana_gid_synced' => $syncedAsanaGid,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function convertIsoDurationToHoursMinutes($isoDuration)
    {
        try {
            // Bersihkan bagian detik agar hanya memiliki detik bulat
            $isoDuration = preg_replace('/\.\d+S/', 'S', $isoDuration);  // Menghapus pecahan detik jika ada

            // Mengonversi string ISO 8601 menjadi DateInterval
            $interval = new DateInterval($isoDuration);

            // Menghitung total jam
            $totalHours = ($interval->d * 24) + $interval->h;
            $totalMinutes = $interval->i;

            // Jika detik lebih dari 30, tambahkan satu menit
            if ($interval->s >= 30) {
                $totalMinutes++;
            }

            // Jika menit lebih dari 60, tambahkan jam
            if ($totalMinutes >= 60) {
                $totalHours += intdiv($totalMinutes, 60);
                $totalMinutes = $totalMinutes % 60;
            }

            // Format hasil
            return sprintf('%02d jam %02d menit', $totalHours, $totalMinutes);
        } catch (Exception $e) {
            // Jika formatnya tidak valid
            return 'Format tidak valid';
        }
    }
    private function convertIsoDurationToMinutes(string $isoDuration): int|null
    {
        try {
            // Hilangkan pecahan detik
            $isoDuration = preg_replace('/\.\d+S/', 'S', $isoDuration);

            // Buat DateInterval
            $interval = new DateInterval($isoDuration);

            // Total menit = hari * 24 * 60 + jam * 60 + menit
            $totalMinutes = ($interval->d * 1440) + ($interval->h * 60) + $interval->i;

            // Tambah 1 menit kalau detik >= 30
            if ($interval->s >= 30) {
                $totalMinutes += 1;
            }

            return (int) $totalMinutes;
        } catch (Exception $e) {
            return null;
        }
    }
}
