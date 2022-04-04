<?php

namespace App\Ldap;

use App\Models\Role;
use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

/**
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database/configuration/#attribute-handlers
 */
class RoleAttributeHandler
{
    /**
     * Define o perfil ordinário para os usuários importados do servidor LDAP.
     *
     * Esse perfil somente é definido para os usuários sem perfil na aplicação.
     * Não há sobrescrita me perfis já definidos.
     *
     * @param \App\Models\User                        $user
     * @param \LdapRecord\Models\ActiveDirectory\User $ldap
     *
     * @return void
     */
    public function handle(LdapUser $ldap, User $user)
    {
        $user->load('role');

        if ($user->role === null) {
            $user->role_id = Role::ORDINARY;
        }
    }
}
