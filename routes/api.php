<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\SubscriptionPlanApiController;
use App\Http\Controllers\Api\JobOfferApiController;
use App\Http\Controllers\Api\JobApplicationApiController;
use App\Http\Controllers\Api\UserSubscriptionApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\CompanyApiController;

// ✅ these two are the ones you actually have
use App\Http\Controllers\Api\PaymentTransactionApiController;
use App\Http\Controllers\Api\PaymentApiController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthApiController::class, 'register']);
    Route::post('login',    [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',          [AuthApiController::class, 'me']);
        Route::post('logout',     [AuthApiController::class, 'logout']);
        Route::post('logout-all', [AuthApiController::class, 'logoutAll']);
    });
});

Route::middleware(['auth:sanctum', 'usage.window'])->group(function () {
    // user + plans
    Route::get('users/me', [UserApiController::class, 'me']);
    Route::patch('users', [UserApiController::class, 'update']);
    Route::get('plans', [SubscriptionPlanApiController::class, 'index']);
    Route::get('plans/{subscription_plan}', [SubscriptionPlanApiController::class, 'show']);
    Route::get('subscription/current-plan', [SubscriptionPlanApiController::class, 'myCurrentPlan']);

    // subscriptions
    Route::get('subscriptions', [UserSubscriptionApiController::class, 'index']);
    Route::get('subscriptions/{user_subscription}', [UserSubscriptionApiController::class, 'show']);
    Route::get('me/subscription/current', [UserSubscriptionApiController::class, 'current']);
    Route::post('subscriptions', [UserSubscriptionApiController::class, 'store']);
    Route::post('subscriptions/manual-from-transaction', [UserSubscriptionApiController::class, 'apiCreateSubscriptionFromManual']);
    Route::patch('subscriptions/{user_subscription}', [UserSubscriptionApiController::class, 'update']);

    // ✅ payments
    Route::post('payment/init-konnect', [PaymentApiController::class, 'initKonnectPayment']); // method lives in PaymentApiController
    Route::post('payment/manual',       [PaymentTransactionApiController::class, 'apiStoreManual']); // manual upload lives here

    // optional transaction CRUD (owner-scoped)
    Route::get ('payment-transactions',      [PaymentTransactionApiController::class, 'index']);
    Route::post('payment-transactions',      [PaymentTransactionApiController::class, 'store']);
    Route::get ('payment-transactions/{id}', [PaymentTransactionApiController::class, 'show']);
    Route::put ('payment-transactions/{id}', [PaymentTransactionApiController::class, 'update']);

    // jobs, companies, categories ...
    Route::get('job-offers', [JobOfferApiController::class, 'index']);
    Route::get('job-offers/meta', [JobOfferApiController::class, 'meta']);
    Route::get('job-offers/{job_offer}', [JobOfferApiController::class, 'show']);

    Route::match(['GET', 'POST'], 'companies/show', [CompanyApiController::class, 'show']);

    Route::get   ('job-applications',      [JobApplicationApiController::class, 'index']);
    Route::get   ('job-applications/{id}', [JobApplicationApiController::class, 'show']);
    Route::post  ('job-applications',      [JobApplicationApiController::class, 'store']);
    Route::match (['put','patch'], 'job-applications/{id}', [JobApplicationApiController::class, 'update']);
    Route::delete('job-applications/{id}', [JobApplicationApiController::class, 'destroy']);

    Route::get('categories',      [CategoryApiController::class, 'index']);
    Route::get('categories/{id}', [CategoryApiController::class, 'show']);
});
