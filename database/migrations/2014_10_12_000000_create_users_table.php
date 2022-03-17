<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Usuários
 *
 * Os usuários são sincronizados com o servidor LDAP.
 *
 * @see https://laravel.com/docs/8.x/migrations
 * @see https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
 * @see https://docs.microsoft.com/pt-br/windows/win32/adschema/a-samaccountname?redirectedfrom=MSDN
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->string('username', 20)->unique();
            $table->string('password');
            $table->string('guid')->unique()->nullable();
            $table->string('domain')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

