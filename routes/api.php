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
//    Route::post('/tokens/create', function (Request $request) {
//        $token = $request->user()->createToken($request->token_name);
//
//        return ['token' => $token->plainTextToken];
//    });
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/token', [AuthController::class, 'token']);
    Route::get('/', [AuthController::class, 'healthcheck']);
    Route::get('/tenantID', [AuthController::class, 'getTenantId'])->middleware(EnsureTokenIsValid::class)->middleware('auth:sanctum');
    Route::put('/tenantID', [AuthController::class, 'tenantId'])->middleware(EnsureTokenIsValid::class)->middleware('auth:sanctum');

    Route::get('/office', [AuthController::class, 'getOffice']);
    Route::put('/office', [AuthController::class, 'updateOffice']);

    Route::get('/folders', [DocumentsController::class, 'index'])->middleware(EnsureTokenIsValid::class)->middleware('auth:sanctum');
    Route::get('/folder/{folder_id}', [DocumentsController::class, 'index'])->middleware(EnsureTokenIsValid::class)->middleware('auth:sanctum');
    Route::get('/file/{id}', [DocumentsController::class, 'index'])->middleware(EnsureTokenIsValid::class)->middleware('auth:sanctum');
});

