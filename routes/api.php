<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsanaController;
use App\Http\Controllers\AsanaWAController;
use App\Http\Controllers\JenkinsController;
use App\Http\Controllers\GithubAutomationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/send-to-wa', [AsanaWAController::class, 'store']);

//Asana Webhook
Route::post('/asana/create-subtask', [AsanaController::class, 'createSubtask']);

//Github Webhook
Route::post('/create-github-issue', [GithubAutomationController::class, 'createIssueGithub']);
Route::post('/jenkins/notify', [JenkinsController::class, 'sendErrorNotification']);
Route::post('/github/make-pr', [GithubAutomationController::class, 'webhook_make_pr']);
Route::post('/wa-manaje/webhook-replied', [GithubAutomationController::class, 'webhook_manaje_wa_replied']);
