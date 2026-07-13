<?php

use App\Http\Controllers\Api\MemberAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->group(function () {
    Route::post('register', [MemberAuthController::class, 'register']);
    Route::post('login', [MemberAuthController::class, 'login']);
    Route::post('refresh', [MemberAuthController::class, 'refresh']);

    Route::middleware('jwt.member')->group(function () {
        Route::post('logout', [MemberAuthController::class, 'logout']);
        Route::get('me', [MemberAuthController::class, 'me']);
        Route::get('find/{member:public_id}', [MemberAuthController::class, 'find']);
    });
});
