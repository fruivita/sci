<?php

namespace App\Policies;

use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class SimulationPolicy extends Policy
{
    /**
     * Determine whether the user can create simulations.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return
            session()->missing('simulated')
            && $this->hasPermissionWithoutCache($user, User::SIMULATION_CREATE);
    }

    /**
     * Determine whether the user can delete the simulation.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user)
    {
        return session()->has('simulator');
    }
}
