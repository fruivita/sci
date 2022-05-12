<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Test\Simulation\SimulationLivewireCreate;
use App\Models\User;
use App\Rules\LdapUser;
use App\Rules\NotCurrentUser;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to simulate a user without being authenticated', function () {
    logout();

    get(route('test.simulation.create'))->assertRedirect(route('login'));
});

test("authenticated but without specific permission, can't undo simulation", function () {
    logout();

    delete(route('test.simulation.destroy'))->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access mock route', function () {
    get(route('test.simulation.create'))->assertForbidden();
});

test('cannot access route to undo the simulation if it does not exist', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    delete(route('test.simulation.destroy'))->assertForbidden();
});

test('cannot render simulation component without specific permission', function () {
    Livewire::test(SimulationLivewireCreate::class)
    ->assertForbidden();
});

test('unable to render component with another simulation in progress', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk();

    get(route('test.simulation.create'))->assertForbidden();
});

test('cannot simulate user with another simulation in progress', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk()
    ->set('username', 'baz')
    ->call('store')
    ->assertForbidden();
});

test('cannot undo simulation if it does not exist', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->call('destroy')
    ->assertForbidden();
});

// Rules
test('username is required', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', '')
    ->call('store')
    ->assertHasErrors(['username' => 'required']);
});

test('username must be a string', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', ['bar'])
    ->call('store')
    ->assertHasErrors(['username' => 'string']);
});

test('username must be a maximum of 20 characters', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', Str::random(21))
    ->call('store')
    ->assertHasErrors(['username' => 'max']);
});

test('username of the simulated user must be different from the authenticated user, because the authenticated user cannot be simulated', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertHasErrors(['username' => NotCurrentUser::class]);
});

test('username must exist on LDAP server', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'bar')
    ->call('store')
    ->assertHasErrors(['username' => LdapUser::class]);
});

// Happy path
test('renders the simulation component with specific permission', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    get(route('test.simulation.create'))
    ->assertOk()
    ->assertSeeLivewire(SimulationLivewireCreate::class);
});

test('simulation creates session variables and redirect to home page', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertRedirect(route('home'));

    expect(session()->get('simulated'))->toBeInstanceOf(User::class)
    ->and(session()->get('simulated')->username)->toBe('foo')
    ->and(session()->get('simulator'))->toBeInstanceOf(User::class)
    ->and(session()->get('simulator')->username)->toBe('bar');
});

test('feedback is displayed to the user when the simulation is active and when it is finished', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->assertDontSee(__('Simulation activated by :attribute', ['attribute' => 'bar']))
    ->call('store');

    get(route('home'))
    ->assertSee(__('Simulation activated by :attribute', ['attribute' => 'bar']));

    delete(route('test.simulation.destroy'));

    get(route('home'))
    ->assertDontSee(__('Simulation activated by :attribute', ['attribute' => 'bar']));
});

test('simulation imports the user into the database', function () {
    logout();
    // User foo already exists in fake LDAP, and also in DB. It is excluded from the DB.
    User::where('username', 'foo')->delete();

    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    expect(User::where('username', 'foo')->first())->toBeEmpty();

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk();

    expect(User::where('username', 'foo')->get())->toHaveCount(1);
});

test('simulation changes the authenticated user and when it ends, it returns to the previous user', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    expect(auth()->user()->username)->toBe('bar');

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store');

    // forces browsing for users to switch to occur.
    get(route('home'));

    expect(auth()->user()->username)->toBe('foo');

    delete(route('test.simulation.destroy'));

    expect(auth()->user()->username)->toBe('bar');
});
