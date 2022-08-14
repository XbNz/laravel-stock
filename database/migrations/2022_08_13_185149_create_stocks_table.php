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
            $table->string('url');
            $table->string('store')->index();
            $table->integer('price');
            $table->string('sku');
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
