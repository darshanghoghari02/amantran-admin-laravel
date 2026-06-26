<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\TemplateApiController;
use App\Http\Controllers\Api\FontApiController;
use App\Http\Controllers\Api\LanguageApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SubscriptionApiController;
use App\Http\Controllers\Api\DraftApiController;
use App\Http\Controllers\Api\FavoriteApiController;
use App\Http\Controllers\Api\GuestApiController;
use App\Http\Controllers\Api\TranslateApiController;
use App\Http\Controllers\Api\UploadApiController;
use App\Http\Controllers\Api\AnalyticsApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\AuditLogApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Diagnostic and Base Route
Route::get('/', [AuthController::class, 'baseInfo']);
Route::get('/diagnose', [AuthController::class, 'diagnose']);

// 1. Mobile App Auth Endpoints (no auth needed for these, suspension checks inside where needed)
Route::prefix('auth')->group(function () {
    Route::post('/send-whatsapp-otp', [AuthController::class, 'sendWhatsappOtp']);
    Route::post('/verify-whatsapp-otp', [AuthController::class, 'verifyWhatsappOtp']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/google-login', [AuthController::class, 'googleLogin']);
    Route::post('/apple-login', [AuthController::class, 'appleLogin']);
});

// 2. Mobile Client App Endpoints (/api/app/*)
// In Node.js backend, these are checked by MaintenanceMode middleware
Route::prefix('app')->group(function () {
    // SSE Realtime connection
    Route::get('/realtime', [AuthController::class, 'realtimeSSE']);
    
    // System Config
    Route::get('/config', [AuthController::class, 'appConfig']);
    
    // Public Catalog
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/languages', [LanguageApiController::class, 'index']);
    Route::get('/templates', [TemplateApiController::class, 'index']);
    Route::get('/templates/{id}', [TemplateApiController::class, 'show']);
    Route::get('/fonts', [FontApiController::class, 'index']);
    Route::get('/subscriptions', [SubscriptionApiController::class, 'index']);
    
    // Mobile User & Drafts endpoints (JWT authenticated for Flutter App)
    Route::middleware(['api.jwt', 'check.suspension'])->group(function () {
        Route::get('/users/resolve/find', [UserApiController::class, 'resolveUser']);
        Route::get('/users/{uid}', [UserApiController::class, 'showAppUser']);
        Route::post('/users', [UserApiController::class, 'saveAppUser']);
        Route::get('/users/{uid}/profile', [UserApiController::class, 'getAppUserProfile']);
        Route::post('/users/{uid}/profile', [UserApiController::class, 'saveAppUserProfile']);
        Route::get('/users/{uid}/settings', [UserApiController::class, 'getAppUserSettings']);
        Route::post('/users/{uid}/settings', [UserApiController::class, 'saveAppUserSettings']);
        
        // Drafts & Completed Cards
        Route::get('/drafts/{userId}', [DraftApiController::class, 'indexDrafts']);
        Route::post('/drafts', [DraftApiController::class, 'saveDraft']);
        Route::delete('/drafts/{draftId}', [DraftApiController::class, 'deleteDraft']);
        
        Route::get('/cards/{userId}', [DraftApiController::class, 'indexCards']);
        Route::post('/cards', [DraftApiController::class, 'saveCard']);
        Route::delete('/cards/{cardId}', [DraftApiController::class, 'deleteCard']);
        
        // Favorites
        Route::get('/favorites/{userId}', [FavoriteApiController::class, 'index']);
        Route::post('/favorites', [FavoriteApiController::class, 'toggle']);
        
        // Guests
        Route::get('/guests/{userId}', [GuestApiController::class, 'index']);
        Route::post('/guests', [GuestApiController::class, 'save']);
        Route::delete('/guests/{guestId}', [GuestApiController::class, 'delete']);
        Route::delete('/guests/clear/{userId}', [GuestApiController::class, 'clearAll']);
        
        // Subscriptions & Transactions
        Route::get('/user-subscriptions/{userId}', [SubscriptionApiController::class, 'getUserSubscription']);
        Route::post('/user-subscriptions/purchase', [SubscriptionApiController::class, 'purchaseSubscription']);
        Route::post('/user-subscriptions/{userId}/cancel', [SubscriptionApiController::class, 'cancelSubscription']);
        Route::post('/user-subscriptions/{userId}/reactivate', [SubscriptionApiController::class, 'reactivateSubscription']);
        Route::post('/user-subscriptions/purchase-template', [SubscriptionApiController::class, 'purchaseTemplate']);
        Route::get('/transactions/{userId}', [SubscriptionApiController::class, 'getUserTransactions']);
        
        // Ratings & Audit Log
        Route::get('/ratings/{userId}', [UserApiController::class, 'getUserRating']);
        Route::post('/ratings', [UserApiController::class, 'saveUserRating']);
        Route::post('/audit-logs', [AuditLogApiController::class, 'store']);
    });
});

// 3. Admin AJAX / CRUD Endpoints (guarded by RequirePermission in middleware/headers)
// Note: We use permission checks directly or fall back to checking the x-user-id header.
Route::post('/users/login', [UserApiController::class, 'adminLogin']); // Admin authentication

Route::middleware(['require.permission:dashboard.view'])->group(function () {
    Route::get('/analytics/summary', [AnalyticsApiController::class, 'summary']);
    Route::get('/analytics/charts', [AnalyticsApiController::class, 'charts']);
    Route::get('/analytics/subscription-summary', [AnalyticsApiController::class, 'subscriptionSummary']);
    Route::get('/transactions/stats/summary', [AnalyticsApiController::class, 'transactionSummary']);
    Route::get('/transactions', [AnalyticsApiController::class, 'transactionsIndex']);
});

Route::middleware([])->group(function () {
    // Categories CRUD
    Route::get('/categories', [CategoryApiController::class, 'index'])->middleware('require.permission:categories.view');
    Route::post('/categories', [CategoryApiController::class, 'store'])->middleware('require.permission:categories.create');
    Route::get('/categories/{id}', [CategoryApiController::class, 'show'])->middleware('require.permission:categories.view');
    Route::put('/categories/{id}', [CategoryApiController::class, 'update'])->middleware('require.permission:categories.edit');
    Route::delete('/categories/{id}', [CategoryApiController::class, 'destroy'])->middleware('require.permission:categories.delete');
    
    // Templates CRUD
    Route::get('/templates', [TemplateApiController::class, 'index'])->middleware('require.permission:templates.view');
    Route::post('/templates', [TemplateApiController::class, 'store'])->middleware('require.permission:templates.create');
    Route::get('/templates/{id}', [TemplateApiController::class, 'show'])->middleware('require.permission:templates.view');
    Route::put('/templates/{id}', [TemplateApiController::class, 'update'])->middleware('require.permission:templates.edit');
    Route::delete('/templates/{id}', [TemplateApiController::class, 'destroy'])->middleware('require.permission:templates.delete');
    
    // Fonts CRUD
    Route::get('/fonts', [FontApiController::class, 'index'])->middleware('require.permission:fonts.view');
    Route::post('/fonts', [FontApiController::class, 'store'])->middleware('require.permission:fonts.create');
    Route::get('/fonts/{id}', [FontApiController::class, 'show'])->middleware('require.permission:fonts.view');
    Route::put('/fonts/{id}', [FontApiController::class, 'update'])->middleware('require.permission:fonts.edit');
    Route::delete('/fonts/{id}', [FontApiController::class, 'destroy'])->middleware('require.permission:fonts.delete');
    
    // Languages CRUD
    Route::get('/languages', [LanguageApiController::class, 'index'])->middleware('require.permission:languages.view');
    Route::post('/languages', [LanguageApiController::class, 'store'])->middleware('require.permission:languages.create');
    Route::get('/languages/{id}', [LanguageApiController::class, 'show'])->middleware('require.permission:languages.view');
    Route::put('/languages/{id}', [LanguageApiController::class, 'update'])->middleware('require.permission:languages.edit');
    Route::delete('/languages/{id}', [LanguageApiController::class, 'destroy'])->middleware('require.permission:languages.delete');
    
    // Users Management CRUD
    Route::get('/users/app-users', [UserApiController::class, 'indexAppUsers'])->middleware('require.permission:users.view');
    Route::put('/users/app-users/{id}', [UserApiController::class, 'updateAppUser']);
    Route::delete('/users/app-users/{id}', [UserApiController::class, 'destroyAppUser'])->middleware('require.permission:users.delete');

    Route::get('/users', [UserApiController::class, 'indexAdmin'])->middleware('require.permission:users.view');
    Route::post('/users', [UserApiController::class, 'storeAdmin'])->middleware('require.permission:users.create');
    Route::get('/users/{id}', [UserApiController::class, 'showAdmin'])->middleware('require.permission:users.view');
    Route::put('/users/{id}', [UserApiController::class, 'updateAdmin'])->middleware('require.permission:users.edit');
    Route::delete('/users/{id}', [UserApiController::class, 'destroyAdmin'])->middleware('require.permission:users.delete');
    
    // Subscriptions CRUD
    Route::get('/subscriptions', [SubscriptionApiController::class, 'index'])->middleware('require.permission:subscriptions.view');
    Route::post('/subscriptions', [SubscriptionApiController::class, 'store'])->middleware('require.permission:subscriptions.create');
    Route::get('/subscriptions/{id}', [SubscriptionApiController::class, 'show'])->middleware('require.permission:subscriptions.view');
    Route::put('/subscriptions/{id}', [SubscriptionApiController::class, 'update'])->middleware('require.permission:subscriptions.edit');
    Route::delete('/subscriptions/{id}', [SubscriptionApiController::class, 'destroy'])->middleware('require.permission:subscriptions.delete');
    
    // Roles CRUD
    Route::get('/roles', [RoleApiController::class, 'index'])->middleware('require.permission:roles.view');
    Route::post('/roles', [RoleApiController::class, 'store'])->middleware('require.permission:roles.create');
    Route::get('/roles/{id}', [RoleApiController::class, 'show'])->middleware('require.permission:roles.view');
    Route::put('/roles/{id}', [RoleApiController::class, 'update'])->middleware('require.permission:roles.edit');
    Route::delete('/roles/{id}', [RoleApiController::class, 'destroy'])->middleware('require.permission:roles.delete');
    
    // Settings CRUD
    Route::get('/settings', [UserApiController::class, 'getSystemConfig'])->middleware('require.permission:settings.view');
    Route::put('/settings', [UserApiController::class, 'saveSystemConfig'])->middleware('require.permission:settings.edit');
    
    // Audit Logs CRUD
    Route::get('/audit-logs', [AuditLogApiController::class, 'index'])->middleware('require.permission:roles.view');
    
    // File Upload API
    Route::prefix('uploads')->group(function () {
        Route::post('/single', [UploadApiController::class, 'uploadSingle']);
        Route::post('/multiple', [UploadApiController::class, 'uploadMultiple']);
        Route::delete('/', [UploadApiController::class, 'deleteFile']);
        Route::get('/status', [UploadApiController::class, 'status']);
    });
    
    // Translation Proxy
    Route::post('/translate', [TranslateApiController::class, 'translate']);
});
