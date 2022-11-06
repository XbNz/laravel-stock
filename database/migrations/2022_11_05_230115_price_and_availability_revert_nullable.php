<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('price')->nullable(false)->change();
            $table->boolean('availability')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            //
        });
    }
};
