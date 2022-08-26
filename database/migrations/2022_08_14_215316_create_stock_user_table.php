<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('stock_user', function (Blueprint $table) {
            $table->foreignId('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['stock_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_user');
    }
};
