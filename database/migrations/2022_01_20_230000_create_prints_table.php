<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * @see https://laravel.com/docs/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://docs.microsoft.com/en-us/windows/win32/fileio/maximum-file-path-limitation
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('printer_id');
            $table->unsignedBigInteger('server_id');

            $table->date('date');
            $table->time('time');
            $table->string('filename', 260)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('pages');
            $table->unsignedBigInteger('copies');
            $table->timestamps();

            $table->unique([
                'date',
                'time',
                'client_id',
                'user_id',
                'printer_id',
                'server_id',
            ]);

            $table
                ->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('printer_id')
                ->references('id')
                ->on('printers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('server_id')
                ->references('id')
                ->on('servers')
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
        Schema::dropIfExists('prints');
    }
};
