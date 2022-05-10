<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://dev.mysql.com/worklog/task/?id=12571
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_site', function (Blueprint $table) {
            $table->unsignedBigInteger('server_id');
            $table->unsignedBigInteger('site_id');
            $table->timestamps();

            $table->unique([
                'server_id',
                'site_id',
            ]);

            $table
                ->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->onDelete('cascade')
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
        Schema::dropIfExists('server_site');
    }
};
