<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/**
 * Trait for importing users from LDAP server.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait ImportableLdapUser
{
    /**
     * It imports the user from the LDAP server into the application database
     * and returns it as an application user.
     *
     * @param string $username
     *
     * @return \App\Models\User|null
     */
    private function importLdapUser(string $username)
    {
        Artisan::call('ldap:import', [
            'provider' => 'users',
            '--no-interaction',
            '--filter' => "(samaccountname={$username})",
            '--attributes' => 'cn,samaccountname',
        ]);

        return User::where('username', $username)->first();
    }
}
