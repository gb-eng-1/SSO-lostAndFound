<?php

use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FoundItemController;
use App\Http\Controllers\Admin\GuestItemController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\MatchedItemController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Student\ClaimController as StudentClaimController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\HelpController;
use App\Http\Controllers\Student\LostReportController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\SearchController as StudentSearchController;
use App\Http\Controllers\Student\StudentItemController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
//  Public landing page
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/', fn () => view('auth.login'))->name('home');

// Developer / tester reference — no auth, not linked from any page, access by URL only
Route::get('/dev-guide', fn () => view('dev-guide'));

// ─────────────────────────────────────────────────────────────────────────────
//  Admin auth
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/admin/login',  [AdminLoginController::class, 'showForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// ─────────────────────────────────────────────────────────────────────────────
//  Student auth
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/student/login',  [StudentLoginController::class, 'showForm'])->name('student.login');
Route::post('/student/login', [StudentLoginController::class, 'login']);
Route::post('/student/logout', [StudentLoginController::class, 'logout'])->name('student.logout');

// ─────────────────────────────────────────────────────────────────────────────
//  Admin pages (all guarded by AdminAuthenticate middleware)
// ─────────────────────────────────────────────────────────────────────────────

Route::prefix('admin')->middleware('admin.auth')->group(function () {

    // Dashboard
    Route::get('/',          [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/summary', [AdminDashboardController::class, 'summary'])->name('admin.dashboard.summary');
    Route::get('/search/suggestions', [AdminSearchController::class, 'suggestions'])->name('admin.search.suggestions');
    Route::get('/item',      [AdminDashboardController::class, 'getItem'])->name('admin.item');      // AJAX ?id=
    Route::post('/encode',   [AdminDashboardController::class, 'encode'])->name('admin.encode');    // AJAX encode item
    Route::post('/link',     [AdminDashboardController::class, 'linkTicket'])->name('admin.link'); // AJAX link ticket

    // Found items (internal)
    Route::get('/found-items',         [FoundItemController::class, 'index'])->name('admin.found');
    Route::get('/found-items/barcode-context', [FoundItemController::class, 'barcodeContext'])->name('admin.found.barcode-context');
    Route::post('/found-items',             [FoundItemController::class, 'store']);
    Route::post('/found-items/lost-report', [FoundItemController::class, 'storeLostReport'])->name('admin.found.lost-report');
    Route::delete('/found-items/{id}', [FoundItemController::class, 'destroy'])->name('admin.found.destroy');
    // POST fallback for environments that block DELETE on web server
    Route::post('/found-items/{id}/cancel', [FoundItemController::class, 'destroy'])->name('admin.found.cancel');

    // Guest items (ID surrenders)
    Route::post('/found-items/guest', [GuestItemController::class, 'store'])->name('admin.found.guest');

    // Matched items / pairing
    Route::get('/matched',             [MatchedItemController::class, 'index'])->name('admin.matched');
    Route::get('/matched/candidates',  [MatchedItemController::class, 'getMatchCandidates']); // AJAX ?id=

    // Claim confirmation
    Route::post('/claim/{id}', [AdminClaimController::class, 'confirm'])->name('admin.claim');

    // History (read-only)
    Route::get('/history', [HistoryController::class, 'index'])->name('admin.history');

    // Lost reports (admin)
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports');
    Route::post('/reports/{id}/cancel', [ReportsController::class, 'cancel'])->name('admin.reports.cancel');

    // Export
    Route::get('/export/dashboard', [ExportController::class, 'dashboard'])->name('admin.export.dashboard');

    // Notifications
    Route::get('/notifications',              [AdminNotificationController::class, 'index'])->name('admin.notifications');
    Route::get('/notifications/recent',       [AdminNotificationController::class, 'recent'])->name('admin.notifications.recent');
    Route::post('/notifications/read-all',    [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
    Route::post('/notifications/{id}/read', [AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
    Route::delete('/notifications/{id}',      [AdminNotificationController::class, 'destroy']);
});

// ─────────────────────────────────────────────────────────────────────────────
//  Student pages (all guarded by StudentAuthenticate middleware)
// ─────────────────────────────────────────────────────────────────────────────

Route::prefix('student')->middleware('student.auth')->group(function () {

    // Dashboard
    Route::get('/', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/dashboard/summary', [StudentDashboardController::class, 'summary'])->name('student.dashboard.summary');
    Route::get('/potential-matches', function () {
        return redirect()->route('student.dashboard');
    })->name('student.potential-matches');

    Route::get('/search/suggestions', [StudentSearchController::class, 'suggestions'])->name('student.search.suggestions');
    Route::get('/item', [StudentItemController::class, 'show'])->name('student.item');

    // Lost reports
    Route::get('/reports',             [LostReportController::class, 'index'])->name('student.reports');
    Route::post('/reports',            [LostReportController::class, 'store']);
    Route::post('/reports/{id}/cancel', [LostReportController::class, 'cancel'])->name('student.reports.cancel');

    // Claim submission
    Route::post('/claim', [StudentClaimController::class, 'store'])->name('student.claim');
    Route::post('/claim-intent', [StudentClaimController::class, 'claimIntent'])->name('student.claim-intent');

    // Help / FAQ
    Route::get('/help', [HelpController::class, 'index'])->name('student.help');

    // Notifications
    Route::get('/notifications',              [StudentNotificationController::class, 'index'])->name('student.notifications');
    Route::get('/notifications/recent',       [StudentNotificationController::class, 'recent'])->name('student.notifications.recent');
    Route::post('/notifications/read-all',    [StudentNotificationController::class, 'markAllRead'])->name('student.notifications.read-all');
    Route::post('/notifications/{id}/read', [StudentNotificationController::class, 'markRead'])->name('student.notifications.read');
    Route::delete('/notifications/{id}',      [StudentNotificationController::class, 'destroy']);

    // Claim history
    Route::get('/claim-history', [StudentClaimController::class, 'history'])->name('student.claim-history');
    Route::get('/claim-detail', [StudentClaimController::class, 'detail'])->name('student.claim-detail');
});
