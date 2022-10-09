<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 1000);
            $table->string('url', 766);
            $table->string('store', 35)->index();
            $table->integer('price')->nullable();
            $table->boolean('availability')->nullable();
            $table->string('sku', 30);
            $table->string('image');
            $table->timestamps();

            $table->index(['store', 'sku']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
