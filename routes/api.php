<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\OfficeController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Ajoutez vos routes API ici

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/token', [AuthController::class, 'token']);
    Route::get('/', [AuthController::class, 'healthcheck']);
    Route::get('/tenantID', [AuthController::class, 'getTenantId']);
    Route::put('/tenantID', [AuthController::class, 'tenantId']);

    Route::get('/office', [AuthController::class, 'getOffice']);
    Route::put('/office', [AuthController::class, 'updateOffice']);

    Route::get('/folders', [DocumentsController::class, 'folders']);
    Route::get('/search_folders', [DocumentsController::class, 'searchFolders']);
    Route::get('/folders/{folder_id}', [DocumentsController::class, 'folderById']);
    Route::put('/folder/{folder_id}/request', [DocumentsController::class, 'insertRequest']);
    Route::get('/request/{request_id}', [DocumentsController::class, 'folderByRequest']);
});

