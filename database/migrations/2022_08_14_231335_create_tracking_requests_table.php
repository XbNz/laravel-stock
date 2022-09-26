<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('tracking_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('url');
            $table->string('store');
            $table->string('tracking_type');
            $table->integer('update_interval')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'url']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_requests');
    }
};
