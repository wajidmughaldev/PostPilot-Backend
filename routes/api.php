<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\CreateOrganizationController;
use App\Http\Controllers\Admin\ListOrganizationAccessRequestsController;
use App\Http\Controllers\Organization\MyOrganizationAccessRequestsController;
use App\Http\Controllers\Organization\StoreOrganizationAccessRequestController;
use App\Http\Controllers\Profile\ShowProfileController;
use App\Http\Controllers\Profile\UpdateAvatarController;
use App\Http\Controllers\Profile\UpdatePasswordController;
use App\Http\Controllers\Profile\UpdateProfileController;
use Illuminate\Support\Facades\Route;

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

Route::post('register', RegisterController::class)->middleware('throttle:6,1');
Route::post('login', LoginController::class)->middleware('throttle:10,1');
Route::get('me', MeController::class);
Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
Route::post('forgot-password', ForgotPasswordController::class)->middleware('throttle:6,1');
Route::post('reset-password', ResetPasswordController::class)->middleware('throttle:6,1');

Route::prefix('auth')->group(function () {
    Route::post('register', RegisterController::class)->middleware('throttle:6,1');
    Route::post('login', LoginController::class)->middleware('throttle:10,1');
    Route::post('forgot-password', ForgotPasswordController::class)->middleware('throttle:6,1');
    Route::post('reset-password', ResetPasswordController::class)->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class);
        Route::get('me', MeController::class);
    });
});

Route::prefix('social-accounts')->group(function () {
    //
});

Route::prefix('posts')->group(function () {
    //
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', ShowProfileController::class);
    Route::patch('profile', UpdateProfileController::class);
    Route::post('profile/avatar', UpdateAvatarController::class);
    Route::patch('profile/password', UpdatePasswordController::class);

    Route::prefix('organization-access-requests')->group(function () {
        Route::post('/', StoreOrganizationAccessRequestController::class);
        Route::get('/me', MyOrganizationAccessRequestsController::class);
    });

    Route::prefix('admin')->middleware('can:access-super-admin')->group(function () {
        Route::get('organization-access-requests', ListOrganizationAccessRequestsController::class);
        Route::post('organizations', CreateOrganizationController::class);
    });
});
