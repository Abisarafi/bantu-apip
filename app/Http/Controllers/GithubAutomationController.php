<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GithubAutomationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function webhook_make_pr(Request $request)
    { // Ambil payload JSON
        $payload = $request->all();

        // Periksa apakah action adalah "opened"
        if (isset($payload['action']) && $payload['action'] === 'opened') {
            // Ambil data yang diperlukan
            $url = $payload['pull_request']['url'] ?? null;
            $htmlUrl = $payload['pull_request']['html_url'] ?? null;
            $username = $payload['pull_request']['user']['login'] ?? null;
            $sourceBranch = $payload['pull_request']['head']['ref'];
            $targetBranch = $payload['pull_request']['base']['ref'];
            $title = $payload['pull_request']['title'] ?? null;

            // Format data untuk notifikasi
            $notificationData = [
                'message' => "Pull Request Baru Dibuka!",
                'title' => $title,
                'username' => $username,
                'html_url' => $htmlUrl,
                'api_url' => $url,
                'source_branch' => $sourceBranch,
                'target_branch' => $targetBranch,
            ];

            // Kirim notifikasi (contoh: ke database, email, atau sistem lain)
            $this->sendNotification($notificationData);

            return response()->json(['message' => 'Pull request notify successfully.'], 200);
        }

        // Jika bukan "opened", abaikan
        return response()->json(['message' => 'No action taken.'], 200);
    }

    /**
     * Contoh fungsi untuk mengirim notifikasi.
     */
    private function sendNotification(array $data)
    {

        $appkey = config('wa-manaje.appkey');
        $authkey =  config('wa-manaje.authkey');
        $defaultPhoneNumber = config('wa-manaje.default_number');
        $templateID = "6885b72f-38e2-4d69-a493-fc6372e6ba3a";

        $waResponse = Http::post('https://wa-web.wesclic.com/api/create-message', [
            'appkey' => $appkey,
            'authkey' => $authkey,
            'to' => $defaultPhoneNumber,
            'template_id' => $templateID,
            'variables' => [
                '{title}' => $data['title'],
                '{html_url}' => $data['html_url'],
                '{username}' => $data['username'],
                '{api_url}' => $data['api_url'],
                '{source_branch}' => $data['source_branch'],
                '{target_branch}' => $data['target_branch'],
            ],
        ]);

        // Periksa apakah permintaan WhatsApp berhasil
        if ($waResponse->successful()) {
            return response()->json([
                'whatsappResponse' => $waResponse->json(),
            ]);
        }

        return response()->json(['error' => 'Failed to send WhatsApp message'], $waResponse->status());

        // Implementasi notifikasi (bisa email, Slack, database, dll.)
        // Contoh: Simpan ke log
        \Log::info('Notification Sent: ', $data);
    }

    public function webhook_manaje_wa_replied(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Ambil `sender`
        $sender = $data['sender'] ?? null;
        $textMessage = $data['payload']['extendedTextMessage']['text'];

        $appkey = config('wa-manaje.appkey');
        $authkey =  config('wa-manaje.authkey');
        $defaultPhoneNumber = config('wa-manaje.default_number');

        // Ambil `merge link API` dari quoted message
        $mergeLink = null;
        if (isset($data['payload']['extendedTextMessage']['contextInfo']['quotedMessage']['conversation'])) {
            $quotedMessage = $data['payload']['extendedTextMessage']['contextInfo']['quotedMessage']['conversation'];

            // Cari link Merge API di quoted message menggunakan regex
            preg_match('/Merge Link API: (https:\/\/api\.github\.com\/[^\s]+)/', $quotedMessage, $matches);
            $mergeLink = $matches[1] ?? null;
        }

        if ($sender != $defaultPhoneNumber) {
            return;
        }

        if ($sender != $defaultPhoneNumber || strtoupper($textMessage) != "YA" || empty($mergeLink)) {
            Http::post('https://wa-web.manaje.id/api/create-message', [
                'appkey' => $appkey,
                'authkey' => $authkey,
                'to' => $defaultPhoneNumber,
                'message' => "Merge tidak dapat dilakukan karena kondisi tidak terpenuhi.",

            ]);
            return response()->json([
                'message' => 'Merge ditolak, tidak sesuai akses.',
                'error' => "Merge tidak dapat dilakukan karena kondisi tidak terpenuhi.",
            ], 403); // Ganti 403 dengan 400 jika memang ada masalah dengan input
        }
        //Lakukan merge
        $pullRequestUrl = $mergeLink;
        $mergeUrl = rtrim($pullRequestUrl, '/') . '/merge';
        $personalAccessToken = config('github.github_pat');

        // Data yang akan dikirimkan
        $data = [
            'commit_title' => 'Merging the Pull Request via WhatsApp',
            'commit_message' => 'Merging the Pull Request after review.',
            'merge_method' => 'merge', // Opsi: 'merge', 'squash', 'rebase'
        ];

        try {
            // Kirim permintaan HTTP PUT ke GitHub API
            $response = Http::withToken($personalAccessToken)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json', // Versi API GitHub
                    'X-GitHub-Api-Version' => '2022-11-28', // Versi API terbaru
                ])
                ->put($mergeUrl, $data);

            // Periksa apakah respons berhasil
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['merged']) && $responseData['merged'] === true) {
                    // Kirim notifikasi
                    Http::post('https://wa-web.manaje.id/api/create-message', [
                        'appkey' => $appkey,
                        'authkey' => $authkey,
                        'to' => $defaultPhoneNumber,
                        'message' => "Baik, PR berhasil di-merge ",

                    ]);
                    return response()->json([
                        'message' => 'Pull Request berhasil di-merge.',
                        'details' => $responseData,
                    ]);
                } else {
                    Http::post('https://wa-web.manaje.id/api/create-message', [
                        'appkey' => $appkey,
                        'authkey' => $authkey,
                        'to' => $defaultPhoneNumber,
                        'message' => "Yah, PR gagal di-merge  ",

                    ]);
                    return response()->json([
                        'message' => 'Gagal merge Pull Request.',
                        'details' => $responseData,
                    ], 400);
                }
            } else {
                // Tangani error respons dari GitHub API
                Http::post('https://wa-web.manaje.id/api/create-message', [
                    'appkey' => $appkey,
                    'authkey' => $authkey,
                    'to' => $defaultPhoneNumber,
                    'message' => "Yah, terjadi kesalahan saat menghubungi GitHub API ",

                ]);
                return response()->json([
                    'message' => 'Terjadi kesalahan saat menghubungi GitHub API.',
                    'error' => $response->json(),
                ], $response->status());
            }
        } catch (Exception $e) {
            // Tangani exception yang tidak terduga
            return response()->json([
                'message' => 'Terjadi kesalahan tidak terduga.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //make an issue
    public function createIssueGithub(Request $request)
    {
        $taskGid = $request['taskGid'];

        try {
            $bearerToken = config('asana.bearer_token');
            $asanaResponse = Http::withToken($bearerToken)->get("https://app.asana.com/api/1.0/tasks/{$taskGid}");
            if (!$asanaResponse->successful()) {
                throw new \Exception('Failed to fetch data: ' . $asanaResponse->body());
            }

            $data = $asanaResponse->json();
            $taskUrl = $data['data']['permalink_url'];

            $architecture = null;
            foreach ($data['data']['custom_fields'] as $field) {
                if ($field['name'] === 'Architecture') {
                    $architecture = $field['display_value'];
                    break;
                }
            }
            $idProject =  $data['data']['projects'][0]['gid'];
            $project = Project::where('gid_project', $idProject)->first();
            $linkGithub = $project->github_repo_link;
            if ($architecture == "BackEnd") {
                $linkGithub = $project->github_repo_link;
            } else if ($architecture == "FrontEnd") {

                $linkGithub = $project->github_repo_link_fe;
            } else if ($architecture == "Mobile") {
                $linkGithub = $project->github_repo_link_mobile;
            } else {
                $project_link = Project::whereHas('project_links', function ($query) use ($architecture) {
                    $query->where('title', $architecture);
                })->first();

                $linkGithub = $project_link->link;
            }

            // Ambil informasi dari respon Asana

            // $picTask = $data['data']['assignee']['name'] ?? 'wesclicdev@gmail.com';
            $userGid = $data['data']['assignee']['gid'];
            $taskTitle = $data['data']['name'] ?? 'No Title';
            $taskNotes = $data['data']['notes'] ?? 'No Title';

            $asanaResponseUserDetail = Http::withToken($bearerToken)->get("https://app.asana.com/api/1.0/users/{$userGid}");
            if (!$asanaResponseUserDetail->successful()) {
                throw new \Exception('Failed to fetch data: ' . $asanaResponseUserDetail->body());
            }
            $dataUserDetail = $asanaResponseUserDetail->json();
            $picTask = $dataUserDetail['data']['email'] ?? 'wesclicdev@gmail.com';

            $issueAssignees = Employee::where("email_asana", $picTask)->first();
            if (!$issueAssignees) {
                throw new \Exception('An error occurred: email assigne tidak ada');
            }
            $response = $this->makeIssue($linkGithub, $taskTitle, $taskNotes, [$issueAssignees->username_github]);

            $jsonData = json_decode($response->getContent(), true);

            $issueUrl = $jsonData['issue']['html_url'];

            if ($jsonData['success'] == true) {
                // Kirim notifikasi ke WhatsApp
                $response = $this->kirimNotifikasiTaskAssign($issueAssignees->nama_lengkap, $issueAssignees->no_telepon, $taskUrl, $issueUrl);
            }

            return $response;
        } catch (\Exception $e) {
            $this->sendErrorNotification($taskTitle, 'An error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function kirimNotifikasiTaskAssign($nama, $phoneNumber, $taskUrl, $issueUrl): \Illuminate\Http\JsonResponse
    {
        $appkey = config('wa-manaje.appkey');
        $authkey =  config('wa-manaje.authkey');

        $templateID = "fdf218ce-503c-4458-93bd-25ece9c1a2ce";

        $dataTemplate = [
            'nama' => $nama,
            'task_url' => $taskUrl,
            'issue_url' => $issueUrl,
        ];

        $waResponse = Http::post('https://wa-web.manaje.id/api/create-message', [
            'appkey' => $appkey,
            'authkey' => $authkey,
            'to' => $phoneNumber,
            'template_id' => $templateID,
            'variables' => [
                '{nama}' => $dataTemplate['nama'],
                '{task_url}' => $dataTemplate['task_url'],
                '{issue_url}' => $dataTemplate['issue_url'],

            ],
        ]);
        // Periksa apakah permintaan WhatsApp berhasil
        if ($waResponse->successful()) {
            return response()->json([
                'whatsappResponse' => $waResponse->json(),
            ]);
        }

        return response()->json(['error' => 'Failed to send WhatsApp message'], $waResponse->status());
    }

    /**
     * Creates a new issue on GitHub.
     *
     * @param string $githubLink
     * @param string $issueTitle
     * @param string $issueBody
     * @param array $issueAssignees
     *
     *  
     *
     * This method takes a GitHub link, an issue title, an issue body and an array of assignees
     * and creates a new issue on GitHub using the GitHub API. It returns a JSON response
     * with the issue data.
     *
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function makeIssue($githubLink, $issueTitle, $issueBody, array $issueAssignees)
    {
        try {
            if (empty($githubLink) || empty($issueTitle)) {
                throw new \InvalidArgumentException('GitHub link and issue title are required.');
            }

            $parsedUrl = parse_url($githubLink);
            if (!isset($parsedUrl['path'])) {
                throw new \InvalidArgumentException('Invalid GitHub link.');
            }

            $pathParts = explode('/', trim($parsedUrl['path'], '/'));
            if (count($pathParts) < 2) {
                throw new \InvalidArgumentException('GitHub link must contain a repository owner and name.');
            }

            $personalAccessToken = config('github.github_pat');
            if (empty($personalAccessToken)) {
                throw new \RuntimeException('GitHub personal access token is not configured.');
            }

            $githubRepoOwner = $pathParts[0];
            $githubRepoName = $pathParts[1];

            $issueLabels = [];
            if (strpos($issueTitle, "TC-") === 0) {
                $issueLabels = ["Bug"];
            } elseif (strpos($issueTitle, "UC-") === 0) {
                $issueLabels = ["Feature"];
            } else {
                $issueLabels = ["Unknown"];
            }

            $githubApiUrl = "https://api.github.com/repos/$githubRepoOwner/$githubRepoName/issues";

            $githubApiData = [
                "title" => $issueTitle,
                "body" => $issueBody,
                "assignees" => $issueAssignees,
                "labels" => $issueLabels,
            ];

            $response = Http::withToken($personalAccessToken)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ])
                ->post($githubApiUrl, $githubApiData);

            if (!$response->successful()) {
                throw new \Exception('Failed to create issue on GitHub: ' . $response->body());
            }

            return response()->json([
                'success' => true,
                'message' => 'Issue created successfully',
                'issue' => $response->json()
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->sendErrorNotification($issueTitle, 'Invalid argument: ' . $e->getMessage());
            \Log::error('Invalid argument: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid input provided.'], 400);
        } catch (\RuntimeException $e) {
            $this->sendErrorNotification($issueTitle, 'Runtime error: ' . $e->getMessage());
            \Log::error('Runtime error: ' . $e->getMessage());
            return response()->json(['error' => 'Configuration error.'], 500);
        } catch (\Exception $e) {
            $this->sendErrorNotification($issueTitle, 'Error creating GitHub issue: ' . $e->getMessage());
            \Log::error('Error creating GitHub issue: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create GitHub issue.'], 500);
        }
    }

    private function sendErrorNotification($taskTitle, $errorMessage)
    {
        $appkey = config('wa-manaje.appkey');
        $authkey =  config('wa-manaje.authkey');
        $defaultPhoneNumber = config('wa-manaje.default_number');


        $waResponse = Http::post('https://wa-web.manaje.id/api/create-message', [
            'appkey' => $appkey,
            'authkey' => $authkey,
            'to' => $defaultPhoneNumber,
            'message' => "Upss, ada error ketika membuat gitub issue\n\nTask: $taskTitle\n\n" . $errorMessage,
        ]);
    }
}
