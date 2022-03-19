<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * Usuários
 *
 * Os usuários são sincronizados com o servidor LDAP.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
class User extends Authenticatable implements LdapAuthenticatable
{
    use HasFactory, Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'guid',
        'domain',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Retorna o usuário autenticado para exibição em tela.
     *
     * @return string
     */
    public function forHumans()
    {
        return $this->username;
    }

    /**
     * Verifica se o usuário possui a permissão informada.
     *
     * @param int $permission_id
     *
     * @return bool
     */
    public function hasPermission(int $permission_id)
    {
        $this->load(['role.permissions' => function ($query) use($permission_id) {
            $query->select('id')->where('id', $permission_id);
        }]);

        return
            $this->role instanceof Role
            && $this->role->permissions->isNotEmpty()
            && $this->role->permissions->first()->id === $permission_id
            ? true
            : false;
    }
}
