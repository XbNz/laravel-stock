<?php

declare(strict_types=1);

use App\Api\Alerts\Controllers\AlertChannelController;
use App\Api\Alerts\Controllers\SendVerificationUrlToAlertChannelController;
use App\Api\Alerts\Controllers\TrackingAlertController;
use App\Api\Alerts\Controllers\VerifyAlertChannelController;
use App\Api\Stocks\Controllers\StockController;
use App\Api\TrackingRequests\Controllers\TrackingRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::name('stock.')->group(function () {
        Route::get('stock/', [StockController::class, 'index'])->name('index');
        Route::get('stock/{stock:uuid}', [StockController::class, 'show'])->name('show');
        Route::delete('stock/{stock:uuid}', [StockController::class, 'destroy'])->name('destroy');
    });

    Route::name('trackingRequest.')->group(function () {
        Route::get('tracking-request/', [TrackingRequestController::class, 'index'])->name('index');
        Route::post('tracking-request/', [TrackingRequestController::class, 'store'])->name('store');
    });

    Route::name('trackingAlert.')->group(function () {
        Route::get('tracking-alert/', [TrackingAlertController::class, 'index'])->name('index');
        Route::get('tracking-alert/{trackingAlert:uuid}', [TrackingAlertController::class, 'show'])->name('show');
        Route::post('tracking-alert/', [TrackingAlertController::class, 'store'])->name('store');
        Route::put('tracking-alert/{trackingAlert:uuid}', [TrackingAlertController::class, 'update'])->name('update');
        Route::delete('tracking-alert/{trackingAlert:uuid}', [TrackingAlertController::class, 'destroy'])->name('destroy');

    });


    Route::name('alertChannel.')->group(function () {
        Route::get('alert-channel/', [AlertChannelController::class, 'index'])->name('index');
        Route::post('alert-channel/', [AlertChannelController::class, 'store'])->name('store');
        Route::get('alert-channel/{alertChannel:uuid}', [AlertChannelController::class, 'show'])->name('show');
        Route::delete('alert-channel/{alertChannel:uuid}', [AlertChannelController::class, 'destroy'])->name('destroy');
        Route::post('alert-channel/{alertChannel:uuid}', SendVerificationUrlToAlertChannelController::class)
            ->name('sendVerification');
    });
});

Route::get('alert-channel/verify/{alertChannel:uuid}', VerifyAlertChannelController::class)
    ->middleware('signed')
    ->name('alertChannel.verify');
