<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\MeetingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/verification', function () {
    return view('verification');
});

Route::get('/verification/{id}', function ($id) {
    return view('verification', ['id' => $id]);
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/workspace/{workspaceId}', function ($workspaceId) {
    return view('workspace', ['workspaceId' => $workspaceId]);
});

Route::get('/workspace/{workspaceId}/{channelId}', function ($workspaceId) {
    return view('workspace', ['workspaceId' => $workspaceId]);
});

Route::get('/workspace/{id}', function ($id) {
    return view('workspace', ['id' => $id]);
});

Route::get('/workspaces/{id}/workspaceUsers', [WorkspaceController::class, 'workspaceUsers']);
Route::get('/workspaces/{id}/permissions', [WorkspaceController::class, 'permissions']);
Route::get('/workspaces/{id}/incomingWebhooks', function ($id) {
    return [];
});
Route::get('/workspaces/{id}/workspaceUserGroups', function ($id) {
    return [];
});
Route::get('/workspaces/{id}/customEmoji', function ($id) {
    return ["customEmojis" => []];
});
Route::get('/workspaces/{id}/workspaceUsers/{userId}/channels/my', [WorkspaceController::class, 'workspaceUsersChannels']);
Route::get('/workspaces/{id}/workspaceUsers/{userId}/threads/unread', function ($id, $userId) {
    return [];
});
Route::get('/workspaces/{id}/workspaceUsers/{userId}/frequentReactions', [WorkspaceController::class, 'workspaceUsersFrequentReactions']);
Route::get('/workspaces/{id}/workspaceUsers/{userId}/scheduledMessages', function ($id, $userId) {
    return ["scheduledMessages" => []];
});
Route::get('/workspaces/{id}/workspaceUsers/{userId}/channels/sections', [WorkspaceController::class, 'workspaceUsersChannelsSections']);
Route::get('/apps', function () {
    return view('apps');
});
Route::get('/workspace/{workspaceId}/workspaces/{id}/apps/installations', [WorkspaceController::class, 'appsInstallations']);
Route::get('/workspaces/{workspaceId}/presence/onlineUsers', [WorkspaceController::class, 'onlineUsers']);

Route::get('/workspaces/{workspaceId}/workspaceUsers/dnd-info', [WorkspaceController::class, 'dndInfo']);

Route::get('/workspace/workspaces/{workspaceId}/apps/installations', [WorkspaceController::class, 'appsInstallations']);

Route::get('/workspaces/{id}/channels/{channelId}/messagesV1', [WorkspaceController::class, 'messagesV1']);

Route::post('/workspaces/{id}/channels/{channelId}/workspaceUsers/{userId}/messages', [WorkspaceController::class, 'createMessage']);

Route::get('/workspaces/{id}/channels/{channelId}', [WorkspaceController::class, 'channel']);

Route::get('/workspaces/{id}/channels/{channelId}/retention', [WorkspaceController::class, 'retention']);
//Route::get('/workspaces/{id}/workspaceUsers/{userId}/inAppNews', [WorkspaceController::class, 'inAppNews']);

Route::post('/workspaces/{id}/permanentCalls', [MeetingController::class, 'permanentCalls']);

Route::get('/workspaces/{id}/workspaceUsers/{userId}/drafts', [WorkspaceController::class, 'drafts']);

Route::get('/feature-list', function () {
    return view('feature-list');
});

Route::get('/create-workspace', function () {
    return view('create-workspace');
});

Route::get("/info", [UsersController::class, 'info']);

Route::get('/meeting/{code}', function ($code) {
    $meeting  = \App\Models\Meeting::where('code', $code)->first();
    if ($meeting) {
        $user = \App\Models\User::where('id', $meeting->workspace_user_id)->first();
        return view('calls', ['code' => $code, 'user' => $user]);
    } else {
        return redirect('/');
    }
});

Route::get('/send-download', [MeetingController::class, 'sendDownload']);