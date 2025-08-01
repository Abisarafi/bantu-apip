<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class JenkinsController extends Controller
{
    public function sendErrorNotification(Request $request)
    {
        $appkey = config('wa-manaje.appkey');
        $authkey =  config('wa-manaje.authkey');
        $defaultPhoneNumber = config('wa-manaje.default_number');
        $secondaryPhoneNumber = config('wa-manaje.secondary_number'); 

        $payload = $request->all();
        $statusMessage = ($payload['status'] == 'success') ? "🟢 Jenkins Build Succeeded!" : "🔴 Jenkins Build Failed!";

        // Format pesan notifikasi
        $message = "$statusMessage .\n"
            . "🚀 App Name: " . ($payload['app_name'] ?? 'Unknown') . "\n"
            . "🛠️ Job Name: " . ($payload['job_name'] ?? 'Unknown') . "\n"
            . "📦 Build Number: " . ($payload['build_number'] ?? 'Unknown') . "\n"
            . "🌿 Git Branch: " . ($payload['git_branch'] ?? 'Unknown') . "\n"
            . "🔗 Commit: " . ($payload['commit'] ?? 'Unknown') . "\n"
            . "⏰ Timestamp: " . ($payload['timestamp'] ?? 'Unknown') . "\n"
            . "🖥️ Node: " . ($payload['node'] ?? 'Unknown') . "\n"
            . "⚠️ Status: " . ($payload['status'] ?? 'Unknown');


        // $waResponse = Http::post('https://wa-web.wesclic.com/api/create-message', [
        //     'appkey' => $appkey,
        //     'authkey' => $authkey,
        //     'to' => $defaultPhoneNumber,
        //     'message' => $message,
        // ]);
        


    // Kirim notifikasi ke nomor utama dan nomor tambahan
    $waResponse = Http::post('https://wa-web.wesclic.com/api/create-message', [
        'appkey' => $appkey,
        'authkey' => $authkey,
        'to' => $defaultPhoneNumber,
        'message' => $message,
    ]);
    // Mengirimkan pesan ke nomor tambahan
    $waResponseSecondary = Http::post('https://wa-web.wesclic.com/api/create-message', [
        'appkey' => $appkey,
        'authkey' => $authkey,
        'to' => $secondaryPhoneNumber,
        'message' => $message,
    ]);


    // Log respons
    if ($waResponse->successful() && $waResponseSecondary->successful()) {
        \Log::info('WhatsApp notifications sent successfully.');
    } else {
        \Log::error('Failed to send WhatsApp notifications.');
    }
    }
}
