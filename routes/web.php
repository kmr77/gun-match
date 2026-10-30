<?php

use App\Http\Controllers\Account\DestroyAccountController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\UpdatePasswordController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MemberDetailsController;
use App\Http\Controllers\Admin\MemberStatusController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OutdoorSampleController;
use App\Http\Controllers\StaticPageController;
use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/categories', CategoryController::class)->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('categories.show');
Route::get('/outdoor-samples', [OutdoorSampleController::class, 'index'])->name('outdoor-samples.index');
Route::get('/outdoor-samples/{slug}', [OutdoorSampleController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('outdoor-samples.show');
Route::get('/admin', AdminDashboardController::class)
    ->middleware(['auth', EnsureAccountIsActive::class, 'verified', 'can:access-admin'])
    ->name('admin.dashboard');
Route::middleware(['auth', EnsureAccountIsActive::class, 'verified', 'can:access-admin'])
    ->prefix('/admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/members/{user}', MemberDetailsController::class)
            ->whereNumber('user')
            ->name('members.show');
        Route::patch('/members/{user}/status', [MemberStatusController::class, 'update'])
            ->whereNumber('user')
            ->name('members.status.update');
    });

Route::controller(StaticPageController::class)->group(function (): void {
    Route::get('/about', 'information')->defaults('page', 'about')->name('about');
    Route::get('/guide', 'information')->defaults('page', 'guide')->name('guide');
    Route::get('/safety', 'information')->defaults('page', 'safety')->name('safety');
    Route::get('/prohibited-items', 'information')->defaults('page', 'prohibited-items')->name('prohibited-items');
    Route::get('/faq', 'information')->defaults('page', 'faq')->name('faq');
    Route::get('/terms', 'information')->defaults('page', 'terms')->name('terms');
    Route::get('/privacy', 'information')->defaults('page', 'privacy')->name('privacy');
    Route::get('/contact', 'information')->defaults('page', 'contact')->name('contact');

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', 'authPreview')->defaults('page', 'login')->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
        Route::get('/register', 'authPreview')->defaults('page', 'register')->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');
        Route::get('/forgot-password', 'authPreview')->defaults('page', 'forgot-password')->name('password.request');
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('password.email');
        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    });

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->middleware(['auth', EnsureAccountIsActive::class])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['auth', EnsureAccountIsActive::class, 'signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware(['auth', EnsureAccountIsActive::class, 'throttle:6,1'])
        ->name('verification.send');

    Route::middleware(['auth', EnsureAccountIsActive::class])->group(function (): void {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/mypage', 'mypage')->defaults('page', 'home')->name('mypage');
        Route::get('/mypage/profile', 'mypage')->defaults('page', 'profile')->name('mypage.profile');
        Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
        Route::get('/mypage/profile/public', 'mypage')->defaults('page', 'profile-public')->name('mypage.profile.public');
        Route::get('/mypage/profile/preview', 'mypage')->defaults('page', 'profile-preview')->name('mypage.profile.preview');
        Route::get('/mypage/settings', 'mypage')->defaults('page', 'settings')->name('mypage.settings');
        Route::get('/mypage/security', 'mypage')->defaults('page', 'security')->name('mypage.security');
        Route::put('/mypage/password', [UpdatePasswordController::class, 'update'])->name('mypage.password.update');
        Route::get('/mypage/account', 'mypage')->defaults('page', 'account')->name('mypage.account');
        Route::delete('/mypage/account', [DestroyAccountController::class, 'destroy'])
            ->middleware('throttle:3,1')
            ->name('mypage.account.destroy');
        Route::get('/mypage/notifications', 'mypage')->defaults('page', 'notifications')->name('mypage.notifications');
        Route::get('/mypage/announcements', 'mypage')->defaults('page', 'announcements')->name('mypage.announcements');
    });
});
