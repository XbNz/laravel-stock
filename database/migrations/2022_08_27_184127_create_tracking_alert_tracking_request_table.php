<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('tracking_alert_tracking_request', function (Blueprint $table) {
            $table->foreignId('tracking_alert_id')->references('id')->on('tracking_alerts')->onDelete('cascade');
            $table->foreignId('tracking_request_id')->references('id')->on('tracking_requests')->onDelete('cascade');
            $table->unique(['tracking_alert_id', 'tracking_request_id'], 'alert_request_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_alert_tracking_request');
    }
};
