<?php

use App\Http\Controllers\Api\AgentApiController;
use App\Http\Controllers\Api\AiValuationController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DevelopmentApiController;
use App\Http\Controllers\Api\IntegrationApiController;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\StayApiController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 1. Mobile Auth Endpoints
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/login', [AuthApiController::class, 'login']);

    Route::middleware(AuthenticateApiToken::class)->group(function () {
        Route::get('/auth/me', [AuthApiController::class, 'me']);
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
        Route::post('/stays/book', [StayApiController::class, 'book']);
        Route::post('/experiences/bookings', [IntegrationApiController::class, 'bookExperience']);
    });

    // 2. Marketplace Properties
    Route::get('/properties', [PropertyApiController::class, 'index']);
    Route::get('/properties/{property}', [PropertyApiController::class, 'show']);

    // 3. Stays & Hospitality
    Route::get('/stays', [StayApiController::class, 'index']);

    // 4. Master Developments
    Route::get('/developments', [DevelopmentApiController::class, 'index']);
    Route::get('/developments/{project}', [DevelopmentApiController::class, 'show']);

    // 5. Verified Agents Directory
    Route::get('/agents', [AgentApiController::class, 'index']);
    Route::get('/agents/{agent}', [AgentApiController::class, 'show']);

    // 6. Experiences & Tours
    Route::get('/experiences', [IntegrationApiController::class, 'experiences']);

    // 7. Algorithmic Automated Valuation Model (AVM)
    Route::post('/ai/valuation', [AiValuationController::class, 'evaluate']);

    // 8. Integrations, Webhooks & Syndication
    Route::get('/syndication/listings.json', [IntegrationApiController::class, 'syndication']);
    Route::post('/mortgage/referral', [IntegrationApiController::class, 'mortgageReferral']);
    Route::post('/webhooks/smart-lock', [IntegrationApiController::class, 'smartLockWebhook']);
    Route::post('/webhooks/utility-bills', [IntegrationApiController::class, 'utilityBillWebhook']);
});
