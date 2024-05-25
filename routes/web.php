<?php

use App\Http\Controllers\WelcomePageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;

// Route for the welcome page, accessible only to guests
Route::get('/', function () {
    return view('welcome');
})->middleware('guest');

// Auth routes with email verification
Auth::routes(['verify' => true]);

// Group routes that are only accessible to guests
Route::middleware('guest')->group(function() {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/complete-registration', [RegisterController::class, 'completeRegistration'])->name('complete.registration');
    Route::get('/captcha/regenerate', [RegisterController::class, 'regenerateCaptcha'])->name('captcha.regenerate');
    Route::get('/captcha/regenerate-login', [LoginController::class, 'regenerateCaptcha'])->name('login.captcha.regenerate');
});

// Group routes that are only accessible to authenticated users
Route::middleware('auth')->group(function() {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Wallet routes
    Route::get('/transactions-history', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/add', [WalletController::class, 'addFunds'])->name('wallet.add');
    Route::post('/wallet/complete-payment', [WalletController::class, 'completePayment'])->name('wallet.completePayment');

    // Event routes
    Route::middleware('check.events')->group(function() {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::post('/events/destroyMultiple', [EventController::class, 'destroyMultiple'])->name('events.destroyMultiple');
        Route::get('/events/data', [EventController::class, 'getEvents'])->name('events.data');
    });

    // Route to create a new event, accessible without event check
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
});

// Route to check Stripe API key, accessible to all
Route::get('/check-stripe-key', function () {
    return response()->json(['stripe_secret' => config('services.stripe.secret')]);
});
