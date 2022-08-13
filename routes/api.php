<?php

declare(strict_types=1);

use App\Api\Stocks\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::name('stock.')->group(function () {
        Route::get('stock/', [StockController::class, 'index'])->name('index');
        Route::get('stock/{stock:uuid}', [StockController::class, 'show'])->name('show');
        Route::put('stock/{stock:uuid}', [StockController::class, 'update'])->name('update');
        Route::delete('stock/{stock:uuid}', [StockController::class, 'destroy']);
    });
});
