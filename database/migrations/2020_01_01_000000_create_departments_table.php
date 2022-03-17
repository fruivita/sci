<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Lotações
 *
 * @see https://laravel.com/docs/8.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('parent_department')->nullable();
            $table->string('name', 255);
            $table->string('acronym', 50);
            $table->timestamps();

            $table->primary('id');

            $table
                ->foreign('parent_department')
                ->references('id')
                ->on('departments')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
};
