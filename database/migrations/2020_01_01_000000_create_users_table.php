<?php

use App\Models\Role;
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
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('duty_id')->nullable();
            $table->unsignedBigInteger('occupation_id')->nullable();
            $table->unsignedBigInteger('role_id')->default(Role::ORDINARY);
            $table->string('name', 255)->nullable();
            $table->string('username', 20)->unique();
            $table->string('password', 255)->nullable();
            $table->string('guid', 255)->unique()->nullable();
            $table->string('domain', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table
                ->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onUpdate('cascade');
            $table
                ->foreign('duty_id')
                ->references('id')
                ->on('duties')
                ->onUpdate('cascade');
            $table
                ->foreign('occupation_id')
                ->references('id')
                ->on('occupations')
                ->onUpdate('cascade');
            $table
                ->foreign('role_id')
                ->references('id')
                ->on('roles')
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
        Schema::dropIfExists('users');
    }
};
