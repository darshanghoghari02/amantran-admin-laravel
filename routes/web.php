<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

// Root redirect to dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Backward compatibility routes for legacy assets paths
Route::get('/assets/uploads/{filename}', function ($filename) {
    $path = storage_path('app/public/uploads/' . $filename);
    if (!file_exists($path)) {
        // Try templates or categories subfolders
        foreach (['templates', 'categories', 'users', 'fonts', 'logos', 'qr'] as $sub) {
            $subPath = storage_path('app/public/uploads/' . $sub . '/' . $filename);
            if (file_exists($subPath)) {
                return response()->file($subPath);
            }
        }
        abort(404);
    }
    return response()->file($path);
});

Route::get('/assets/fonts/{filename}', function ($filename) {
    $path = storage_path('app/public/uploads/fonts/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

// Route to serve any static assets (like categories and template images) 
// that might be stored in the public folder but not directly accessible
// (e.g. on shared hosting environments where public path differs).
Route::get('/assets/{path}', function ($path) {
    $fullPath = public_path('assets/' . $path);
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');

Route::prefix('admin')->group(function () {
    // Session authentication endpoints
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Panel views
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/', function () { 
            return redirect()->route('admin.dashboard'); 
        });
        
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/categories', function () {
            return view('admin.categories.index');
        })->name('admin.categories');

        Route::get('/templates', function () {
            return view('admin.templates.index');
        })->name('admin.templates');

        Route::get('/templates/editor/{id?}', function ($id = null) {
            return view('admin.templates.editor', ['id' => $id]);
        })->name('admin.templates.editor');

        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('admin.users');

        Route::get('/subscriptions', function () {
            return view('admin.subscriptions.index');
        })->name('admin.subscriptions');

        Route::get('/fonts', function () {
            return view('admin.fonts.index');
        })->name('admin.fonts');

        Route::get('/languages', function () {
            return view('admin.languages.index');
        })->name('admin.languages');

        Route::get('/roles', function () {
            return view('admin.roles.index');
        })->name('admin.roles');

        Route::get('/audit-logs', function () {
            return view('admin.audit-logs.index');
        })->name('admin.audit-logs');

        Route::get('/settings', function () {
            return view('admin.settings.index');
        })->name('admin.settings');
    });
});
