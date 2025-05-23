<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkspaceController;
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



Route::post('/lead', [UsersController::class, 'store']);
Route::post('/lead/activate', [UsersController::class, 'activate']);
Route::post('/lead/login', [UsersController::class, 'login']);
Route::post('/createWorkspace', [WorkspaceController::class, 'createWorkspace']);
