<?php

namespace App\Models;

use FruiVita\Corporate\Models\Department as CorporateDepartment;

/**
 * Lotação de um determinado usuário.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Department extends CorporateDepartment
{
    /**
     * Impressões vindas de uma determinada lotação.
     *
     * Relacionamento lotação (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'department_id', 'id');
    }
}
