<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('search_id')->nullable()->references('id')->on('searches');
            $table->string('url');
            $table->string('store')->index();
            $table->integer('price');
            $table->string('sku');
            $table->string('image');
            $table->integer('update_interval');
            $table->timestamps();

            $table->index(['user_id', 'store', 'sku']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
