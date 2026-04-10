<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StudentReportController;
use App\Http\Controllers\Api\SupportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api automatically via bootstrap/app.php.
|
*/

// ── Authentication ────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/admin/login',   [AuthController::class, 'adminLogin']);
    Route::post('/student/login', [AuthController::class, 'studentLogin']);
    Route::post('/logout',        [AuthController::class, 'logout']);
    Route::get('/me',             [AuthController::class, 'me']);
});

// ── Admin: Items ──────────────────────────────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/items',               [AdminItemController::class, 'index']);
    Route::get('/items/{id}',          [AdminItemController::class, 'show']);
    Route::get('/stats',               [AdminItemController::class, 'stats']);
    Route::put('/items/{id}/status',   [AdminItemController::class, 'updateStatus']);
});

// ── Student: Lost Reports ─────────────────────────────────────────────────
Route::prefix('student')->group(function () {
    Route::get('/reports',                         [StudentReportController::class, 'index']);
    Route::get('/reports/{id}',                    [StudentReportController::class, 'show']);
    Route::match(['get', 'post'], '/reports/match-candidates', [StudentReportController::class, 'matchCandidates']);
});

// ── Notifications ─────────────────────────────────────────────────────────
Route::prefix('notifications')->group(function () {
    Route::get('/',               [NotificationController::class, 'index']);
    Route::post('/',              [NotificationController::class, 'store']);
    Route::get('/unread-count',   [NotificationController::class, 'unreadCount']);
    Route::patch('/read-all',     [NotificationController::class, 'markAllRead']);
    Route::patch('/{id}/read',    [NotificationController::class, 'markRead']);
    Route::delete('/{id}',        [NotificationController::class, 'destroy']);
});

// ── Support (contacts & process guides) ───────────────────────────────────
Route::prefix('support')->group(function () {
    Route::get('/contacts',          [SupportController::class, 'contacts']);
    Route::get('/guides',            [SupportController::class, 'guides']);
    Route::get('/guides/{section}',  [SupportController::class, 'guidesBySection']);
});
