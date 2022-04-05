<?php

namespace App\Models;

use App\Enums\PermissionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * Os usuários são sincronizados com o servidor LDAP.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
class User extends Authenticatable implements LdapAuthenticatable
{
    use HasFactory;
    use Notifiable;
    use AuthenticatesWithLdap;

    protected $table = 'users';

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

    /**
     * Relacionamento usuário (N:1) perfil.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
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
     * Ordenação padrão do modelo.
     *
     * Ordem:
     * - 1º Nome por ordem alfabética asc
     * - 2º Nomes com valor nulo
     * - Critério de desempate: username asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @see https://learnsql.com/blog/how-to-order-rows-with-nulls/
     */
    public function scopeDefaultOrder(Builder $query)
    {
        return $query
                ->orderByRaw('name IS NULL')
                ->orderBy('name', 'asc')
                ->orderBy('username', 'asc');
    }

    /**
     * Verifica se o usuário possui a permissão informada.
     *
     * @param \App\Enums\PermissionType $permission
     *
     * @return bool
     */
    public function hasPermission(PermissionType $permission)
    {
        $this->load(['role.permissions' => function ($query) use ($permission) {
            $query->select('id')->where('id', $permission->value);
        }]);

        return
            $this->role instanceof Role
            && $this->role->permissions->isNotEmpty()
            && $this->role->permissions->first()->id === $permission->value
            ? true
            : false;
    }

    /**
     * Registros filtrados pelo termo informado.
     *
     * O filtro se aplica à sigla e ao nome do usuário por meio de cláusula OR.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null                           $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch(Builder $query, string $term = null)
    {
        return $query->when($term, function ($query, $term) {
            $query
                ->where('username', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
