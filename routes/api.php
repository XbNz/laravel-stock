<?php

declare(strict_types=1);

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
});
