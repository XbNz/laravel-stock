<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('stock_tracking_request', function (Blueprint $table) {
            $table->foreignId('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreignId('tracking_request_id')->references('id')->on('tracking_requests')->onDelete('cascade');
            $table->unique(['stock_id', 'tracking_request_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_tracking_request');
    }
};
