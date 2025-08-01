<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsanaController extends Controller
{
    public function createSubtask(Request $request)
    {
        $taskGid = $request['taskGid'];

        try {
            $bearerToken = config('asana.bearer_token');
            $gidStoryPoint = config('asana.gid_story_point');

            // Ambil story Point dari custom fields
            $storyPoint = null;
            $architecture = null;
            foreach ($request['custom_fields'] as $field) {
                if ($field['name'] === 'Story Point') {
                    $storyPoint = $field['number_value'];
                    break;
                }
            }

            foreach ($request['custom_fields'] as $field) {
                if ($field['name'] === 'Architecture') {
                    $architecture = $field['display_value'];
                    break;
                }
            }

            // Ambil assignee dari task utama
            $taskDetailResp = Http::withToken($bearerToken)
                ->get("https://app.asana.com/api/1.0/tasks/{$taskGid}");

            if ($taskDetailResp->failed()) {
                throw new \Exception("Gagal mengambil data task utama: " . $taskDetailResp->body());
            }

            $taskData = $taskDetailResp->json()['data'];
            $assigneeGid = $taskData['assignee']['gid'] ?? null;


            if (!$storyPoint) {
                return response()->json(['error' => 'Story Point tidak ditemukan atau bernilai nol'], 400);
            }

            // Custom field ID untuk Story Point di Asana (sesuaikan!)

            if (
                $architecture ==
                'Mobile' || $architecture == 'FrontEnd'
            ) {
                $subtasks = [
                    [
                        'name' => 'Integration Real API No Bug',
                        'percentage' => 30
                    ],
                    [
                        'name' => 'Integration Mock Server',
                        'percentage' => 30
                    ],
                    [
                        'name' => 'Slicing',
                        'percentage' => 40
                    ],

                ];
            } else if ($architecture == 'BackEnd' || $architecture == 'Node JS' || $architecture == 'Python') {
                $subtasks = [
                    [
                        'name' => 'No Bug After Implement FE',
                        'percentage' => 10
                    ],
                    [
                        'name' => 'Postman Documentation',
                        'percentage' => 20
                    ],
                    [
                        'name' => 'Logic, Flow & Database',
                        'percentage' => 70
                    ],

                ];
            } else {
                $subtasks = [
                    [
                        'name' => 'Integration No Bug',
                        'percentage' => 20
                    ],
                    [
                        'name' => 'Controller',
                        'percentage' => 40
                    ],
                    [
                        'name' => 'Slicing FE',
                        'percentage' => 40
                    ],


                ];
            }

            // Daftar subtask dengan persentase masing-masing


            foreach ($subtasks as $item) {
                // Hitung dan bulatkan nilai story point
                $calculatedPoint = ($storyPoint * ($item['percentage'] / 100));


                $payload = [
                    'name' => $item['name'],
                    'custom_fields' => [
                        $gidStoryPoint => $calculatedPoint
                    ]
                ];

                if ($assigneeGid) {
                    $payload['assignee'] = $assigneeGid;
                }

                $response = Http::withToken($bearerToken)
                    ->acceptJson()
                    ->post("https://app.asana.com/api/1.0/tasks/{$taskGid}/subtasks", [
                        'data' => $payload
                    ]);

                if ($response->failed()) {
                    throw new \Exception("Failed to create subtask: {$item['name']}. Response: " . $response->body());
                }
            }

            // ✅ Set nilai story point task utama menjadi 0
            Http::withToken($bearerToken)
                ->acceptJson()
                ->put("https://app.asana.com/api/1.0/tasks/{$taskGid}", [
                    'data' => [
                        'custom_fields' => [
                            $gidStoryPoint => 0
                        ]
                    ]
                ]);


            return response()->json([
                'message' => 'All subtasks created successfully with calculated story points'
            ], 200);
        } catch (\Exception $e) {
            $defaultPhoneNumber = config('wa-manaje.default_number');
            Http::post('https://wa-web.manaje.id/api/create-message', [
                'appkey' => config('wa-manaje.appkey'),
                'authkey' => config('wa-manaje.authkey'),
                'to' => $defaultPhoneNumber,
                'message' => 'Error Create Subtask: ' . $e->getMessage(),
            ]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
