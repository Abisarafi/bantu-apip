<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsanaWAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $taskGid = $request['taskGid'];

        try {
            $bearerToken = config('asana.bearer_token');
            $asanaResponse = Http::withToken($bearerToken)->get("https://app.asana.com/api/1.0/tasks/{$taskGid}");

            if (!$asanaResponse->successful()) {
                return response()->json(['error' => 'Failed to fetch data'], $asanaResponse->status());
            }

            $data = $asanaResponse->json();

            // Ambil informasi dari respon Asana
            $projectName = $data['data']['projects'][0]['name'] ?? 'Unknown Project';
            $picTask = $data['data']['assignee']['name'] ?? 'Unassigned';
            $taskTitle = $data['data']['name'] ?? 'No Title';
            $taskLink = $data['data']['permalink_url'] ?? '';

            $pemberiTask = $data['data']['followers'][0]['name'] ?? 'Tidak ada Pemberi Task';

            // Ambil nomor WhatsApp dari custom fields
            $waNumber = null;
            foreach ($data['data']['custom_fields'] as $field) {
                if ($field['name'] === 'WA Pemberi Tugas') {
                    $waNumber = $field['text_value'];
                    break;
                }
            }

            //Cari no wa dari database berdasar email di Asana
            $employeePIC = Employee::where('email_asana', $pemberiTask)->first();
            if ($employeePIC) {
                $waNumber = $employeePIC->no_telepon;
            }

            $waNumber = $this->convertToIndonesiaFormat($waNumber);
            $defaultPhoneNumber = config('wa-manaje.default_number');
            // Kirim pesan WhatsApp
            $waResponse = Http::post('https://wa-web.wesclic.com/api/create-message', [
                'appkey' => config('wa-manaje.appkey'),
                'authkey' => config('wa-manaje.authkey'),
                'to' => $waNumber ?? $defaultPhoneNumber,
                'template_id' => 'baa5e7d5-e3d1-4cb9-868b-b8fbc35d3eca',
                'variables' => [
                    '{status}' => $waNumber ? '' : '*Tugas tidak diberi Nomor PIC Pemberi Tugas*',
                    '{projectName}' => $projectName,
                    '{picTask}' => $picTask,
                    '{taskTitle}' => $taskTitle,
                    '{taskLink}' => $taskLink,
                ],
            ]);

            // Periksa apakah permintaan WhatsApp berhasil
            if ($waResponse->successful()) {
                return response()->json([
                    'projectName' => $projectName,
                    'picTask' => $picTask,
                    'taskTitle' => $taskTitle,
                    'taskLink' => $taskLink,
                    'whatsappResponse' => $waResponse->json(),
                ]);
            }

            return response()->json(['error' => 'Failed to send WhatsApp message'], $waResponse->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function convertToIndonesiaFormat($number)
    {
        // Pastikan nomor tidak null dan dimulai dengan 0
        if ($number && strpos($number, '0') === 0) {
            // Menghapus angka 0 di depan dan menambahkan kode negara
            return '62' . substr($number, 1);
        }
        return $number; // Kembalikan nomor yang tidak diubah jika tidak memenuhi syarat
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

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
}
