<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Simulation\SimulationLivewireCreate;
use App\Models\User;
use App\Rules\LdapUser;
use App\Rules\NotCurrentUser;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('não é possível simular usuário sem estar autenticado', function () {
    logout();

    get(route('simulation.create'))->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível desfazer simulação', function () {
    logout();

    delete(route('simulation.destroy'))->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de simulação', function () {
    get(route('simulation.create'))->assertForbidden();
});

test('não é possível executar a rota para desfazer a simulação se ela não existir', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    delete(route('simulation.destroy'))->assertForbidden();
});

test('não é possível renderizar o componente de simulação sem permissão específica', function () {
    Livewire::test(SimulationLivewireCreate::class)
    ->assertForbidden();
});

test('não é possível renderizar o componente com outra simulação em andamento', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertOk();

    get(route('simulation.create'))->assertForbidden();
});

test('não é possível simular usuário com outra simulação em andamento', function () {
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

test('não é possível desfazer simulação se ela não existir', function () {
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->call('destroy')
    ->assertForbidden();
});

// Rules
test('username do simulado é obrigatório', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', '')
    ->call('store')
    ->assertHasErrors(['username' => 'required']);
});

test('username do simulado deve ser uma string', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', ['bar'])
    ->call('store')
    ->assertHasErrors(['username' => 'string']);
});

test('username do simulado deve ter no máximo 20 caracteres', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', Str::random(21))
    ->call('store')
    ->assertHasErrors(['username' => 'max']);
});

test('username do simulado deve ser diferente do usuário autenticado, pois não se pode simular o próprio usuário', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store')
    ->assertHasErrors(['username' => NotCurrentUser::class]);
});

test('username do simulado deve existir no servidor LDAP', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'bar')
    ->call('store')
    ->assertHasErrors(['username' => LdapUser::class]);
});

// Happy path
test('é possível renderizar o componente de simulação com permissão específica', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    get(route('simulation.create'))
    ->assertOk()
    ->assertSeeLivewire(SimulationLivewireCreate::class);
});

test('simulação cria as variáveis de sessão e redireciona à pagina home', function () {
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

test('feedback é exibido ao usuário quando a simulação está ativa e quando ela é finalizada', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->assertDontSee(__('Simulation activated by :attribute', ['attribute' => 'bar']))
    ->call('store');

    get(route('home'))
    ->assertSee(__('Simulation activated by :attribute', ['attribute' => 'bar']));

    delete(route('simulation.destroy'));

    get(route('home'))
    ->assertDontSee(__('Simulation activated by :attribute', ['attribute' => 'bar']));
});

test('simulação importa o usuário para o banco de dados', function () {
    logout();
    // User foo já existe no fake LDAP, e também no BD. Exclui-se ele do BD.
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

test('simulação troca o usuário autenticado e ao finalizá-la, volta ao usuário anterior', function () {
    logout();
    login('bar');
    grantPermission(PermissionType::SimulationCreate->value);

    expect(auth()->user()->username)->toBe('bar');

    Livewire::test(SimulationLivewireCreate::class)
    ->set('username', 'foo')
    ->call('store');

    // força a navegação para a troca dos usuários ocorrer.
    get(route('home'));

    expect(auth()->user()->username)->toBe('foo');

    delete(route('simulation.destroy'));

    expect(auth()->user()->username)->toBe('bar');
});
