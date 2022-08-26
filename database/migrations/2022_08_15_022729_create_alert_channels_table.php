<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('alert_channels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('type');
            $table->string('value');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'type', 'value']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('alert_channels');
    }
};
