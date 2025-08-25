<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserSubscriptionController;
use App\Http\Controllers\UserAccessLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobOfferController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\CategoryController;

Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');

    // Accept POST on "/"
    Route::post('/', [AuthController::class, 'login'])->name('login.perform');

    // (optional) keep classic /login working
    Route::get('/login', fn() => redirect()->route('login'));
    Route::post('/login', fn() => redirect()->route('login.perform'));
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Subscription Plans routes
    Route::get('subscription-plans/data', [SubscriptionPlanController::class, 'data'])->name('subscription-plans.data');
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::patch('subscription-plans/{subscription_plan}/toggle', [SubscriptionPlanController::class, 'toggleActive'])
        ->name('subscription-plans.toggle');

    // User Access Logs routes
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class);
    // User Subscription routes
    Route::get('user-subscriptions/data', [UserSubscriptionController::class, 'data'])->name('user-subscriptions.data');
    Route::resource('user-subscriptions', UserSubscriptionController::class);
    Route::resource('user-access-logs', UserAccessLogController::class);
    // Companies routes
    Route::get('companies/data', [CompanyController::class, 'data'])->name('companies.data');
    Route::resource('companies', CompanyController::class);
    Route::patch('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');
    Route::patch('companies/{company}/toggle-verified', [CompanyController::class, 'toggleVerified'])->name('companies.toggle-verified');
    // Job Offers routes
    Route::get('job-offers/data', [JobOfferController::class, 'data'])->name('job-offers.data');
    Route::resource('job-offers', JobOfferController::class);
    Route::patch('job-offers/{job_offer}/toggle-status', [JobOfferController::class, 'toggleStatus'])->name('job-offers.toggle-status');
    Route::patch('job-offers/{job_offer}/toggle-featured', [JobOfferController::class, 'toggleFeatured'])->name('job-offers.toggle-featured');
    // Job Applications routes
    Route::get('job-applications/data', [JobApplicationController::class, 'data'])->name('job-applications.data');
    Route::resource('job-applications', JobApplicationController::class);
    Route::patch('job-applications/{job_application}/set-status', [JobApplicationController::class, 'setStatus'])->name('job-applications.set-status');
    Route::patch('job-applications/{job_application}/mark-reviewed', [JobApplicationController::class, 'markReviewed'])->name('job-applications.mark-reviewed');
    // Payment Transactions routes
    Route::get('payment-transactions/data', [PaymentTransactionController::class, 'data'])->name('payment-transactions.data');
    Route::resource('payment-transactions', PaymentTransactionController::class);
    Route::patch('payment-transactions/{payment_transaction}/set-status', [PaymentTransactionController::class, 'setStatus'])
        ->name('payment-transactions.set-status');
    // System Settings routes
    Route::get('system-settings/data', [SystemSettingController::class, 'data'])->name('system-settings.data');
    Route::resource('system-settings', SystemSettingController::class);
    Route::patch('system-settings/{system_setting}/toggle-public', [SystemSettingController::class, 'togglePublic'])->name('system-settings.toggle-public');
    // Categories routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');      
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Route::resource('categories', CategoryController::class);

    


    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Route::get('/', fn () => view('welcome'));
