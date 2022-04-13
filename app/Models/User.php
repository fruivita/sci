<?php

namespace App\Models;

use App\Enums\PermissionType;
use FruiVita\Corporate\Models\User as CorporateUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * Os usuários são sincronizados com o servidor LDAP.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
class User extends CorporateUser implements LdapAuthenticatable
{
    use Notifiable;
    use AuthenticatesWithLdap;

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
        'department_id',
        'occupation_id',
        'duty_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var string[]
     */
    protected $with = ['role'];

    /**
     * Perfil de um usuário.
     *
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
     * Usuário que delegou o perfil para outrém.
     *
     * Relacionamento delegados (N:1) delegante.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delegator()
    {
        return $this->belongsTo(User::class, 'role_granted_by', 'id');
    }

    /**
     * Usuários com poderes (perfis) delegados por outrém.
     *
     * Relacionamento delegante (1:N) delegados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function delegatedUsers()
    {
        return $this->hasMany(User::class, 'role_granted_by', 'id');
    }

    /**
     * Revoga a delegação de perfil do usuário, bem como as que ele delegou,
     * retornando todos ao perfil ordinário.
     *
     * @return bool
     */
    public function revokeDelegation()
    {
        $this->role()->associate(Role::ORDINARY);

        return $this->updateAndRevokeDelegatedUsers();
    }

    /**
     * Revoga as delegações feita pelo usuário.
     *
     * @return int
     */
    public function revokeDelegatedUsers()
    {
        return $this
        ->delegatedUsers()
        ->update([
            'role_granted_by' => null,
            'role_id' => Role::ORDINARY
        ]);
    }

    /**
     * Atualiza as propriedades do usuário e remove as delegações feitas por
     * ele.
     *
     * @return bool
     */
    public function updateAndRevokeDelegatedUsers()
    {
        try {
            DB::beginTransaction();

            $this->delegator()->dissociate();

            $this->save();

            $this->revokeDelegatedUsers();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                __('User update failed'),
                [
                    'exception' => $th,
                ]
            );

            return false;
        }
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
