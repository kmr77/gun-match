<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OutdoorSampleController;
use App\Http\Controllers\StaticPageController;
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

Route::controller(StaticPageController::class)->group(function (): void {
    Route::get('/about', 'information')->defaults('page', 'about')->name('about');
    Route::get('/guide', 'information')->defaults('page', 'guide')->name('guide');
    Route::get('/safety', 'information')->defaults('page', 'safety')->name('safety');
    Route::get('/prohibited-items', 'information')->defaults('page', 'prohibited-items')->name('prohibited-items');
    Route::get('/faq', 'information')->defaults('page', 'faq')->name('faq');
    Route::get('/terms', 'information')->defaults('page', 'terms')->name('terms');
    Route::get('/privacy', 'information')->defaults('page', 'privacy')->name('privacy');
    Route::get('/contact', 'information')->defaults('page', 'contact')->name('contact');

    Route::get('/login', 'authPreview')->defaults('page', 'login')->name('login');
    Route::get('/register', 'authPreview')->defaults('page', 'register')->name('register');
    Route::get('/forgot-password', 'authPreview')->defaults('page', 'forgot-password')->name('password.request');

    Route::get('/mypage', 'mypage')->defaults('page', 'home')->name('mypage');
    Route::get('/mypage/profile', 'mypage')->defaults('page', 'profile')->name('mypage.profile');
    Route::get('/mypage/profile/public', 'mypage')->defaults('page', 'profile-public')->name('mypage.profile.public');
    Route::get('/mypage/profile/preview', 'mypage')->defaults('page', 'profile-preview')->name('mypage.profile.preview');
    Route::get('/mypage/settings', 'mypage')->defaults('page', 'settings')->name('mypage.settings');
    Route::get('/mypage/security', 'mypage')->defaults('page', 'security')->name('mypage.security');
    Route::get('/mypage/account', 'mypage')->defaults('page', 'account')->name('mypage.account');
    Route::get('/mypage/notifications', 'mypage')->defaults('page', 'notifications')->name('mypage.notifications');
    Route::get('/mypage/announcements', 'mypage')->defaults('page', 'announcements')->name('mypage.announcements');
});
