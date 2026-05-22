<?php

use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\WebServiceLogController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\TicketCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Customer\TicketController as CustomerTicketController;
use App\Http\Controllers\Api\V1\Admin\TicketController as AdminTicketController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group.
|
*/

// Auth Routes (no role needed)
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'me']);
    });
});

// Protected API v1 Routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Customer Routes
    Route::prefix('customer')->middleware('role:customer')->group(function () {
        Route::get('tickets', [CustomerTicketController::class, 'index']);
        Route::post('tickets', [CustomerTicketController::class, 'store']);
        Route::get('tickets/{ticket}', [CustomerTicketController::class, 'show']);
        Route::post('tickets/{ticket}/reply', [CustomerTicketController::class, 'reply']);
        Route::get('ticket-categories', [TicketCategoryController::class, 'index']);
        Route::get('notifications/{user}', [NotificationController::class, 'index']);
        Route::get('notifications/{user}/unread', [NotificationController::class, 'unread']);
        Route::get('notifications/{user}/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/{id}/{user}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('notifications/{user}/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    });

    Route::prefix('admin')->group(function () {

        // Shared routes (both levels)
        Route::get('tickets', [AdminTicketController::class, 'index'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::get('tickets/{ticket}', [AdminTicketController::class, 'show'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::post('tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::post('tickets/{ticket}/reject', [AdminTicketController::class, 'reject'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::post('tickets/{ticket}/pending', [AdminTicketController::class, 'pending'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::get('notifications/{user}/unread', [NotificationController::class, 'unread'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::get('notifications/{user}', [NotificationController::class, 'index'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::post('notifications/{id}/{user}/mark-as-read', [NotificationController::class, 'markAsRead'])
            ->middleware('role:admin-level-1|admin-level-2');
        Route::post('notifications/{user}/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
            ->middleware('role:admin-level-1|admin-level-2');

        // Level 1 specific routes
        Route::post('tickets/{ticket}/pending-level2', [AdminTicketController::class, 'moveToPendingLevel2'])
            ->middleware('role:admin-level-1');

        // Level 2 specific routes
        Route::get('users', [AdminUserController::class, 'index'])
            ->middleware('role:admin-level-2');
        Route::post('tickets/{ticket}/close', [AdminTicketController::class, 'close'])
            ->middleware('role:admin-level-2');
        Route::get('web-service-logs', [WebServiceLogController::class, 'index'])
            ->middleware('role:admin-level-2');

    });

});
