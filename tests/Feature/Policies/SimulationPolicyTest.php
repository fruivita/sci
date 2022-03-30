<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\User;
use App\Policies\SimulationPolicy;
use Illuminate\Support\Facades\Cache;

// Forbidden
test('usuário sem permissão não pode criar uma simulação', function () {
    $user = login('foo');

    expect((new SimulationPolicy)->create($user))->toBeFalse();

    logout();
});

test('usuário não pode criar, simultaneamente, duas simulações na mesma sessão', function () {
    $user = login('foo');
    grantPermission(User::SIMULATION_CREATE);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->create($user))->toBeFalse();

    logout();
});

test('usuário não pode desfazer uma simulação se ela não existe em sua sessão', function () {
    $user = login('foo');

    expect((new SimulationPolicy)->delete($user))->toBeFalse();

    logout();
});

// Happy path
test('permissão de criar uma simulação não é persistida em cache', function () {
    $user = login('foo');
    grantPermission(User::SIMULATION_CREATE);

    $key = authenticatedUser()->username . User::SIMULATION_CREATE;

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($user))->toBeTrue()
    ->and(Cache::missing($key))->toBeTrue();

    revokePermission(User::SIMULATION_CREATE);

    expect(Cache::missing($key))->toBeTrue()
    ->and((new SimulationPolicy)->create($user))->toBeFalse()
    ->and(Cache::missing($key))->toBeTrue();

    logout();
});

test('usuário com permissão pode criar uma simulação', function () {
    $user = login('foo');
    grantPermission(User::SIMULATION_CREATE);

    expect((new SimulationPolicy)->create($user))->toBeTrue();

    logout();
});

test('usuário pode desfazer uma simulação se ela existe em sua sessão', function () {
    $user = login('foo');
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy)->delete($user))->toBeTrue();

    logout();
});
