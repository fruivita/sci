<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Log\LogLivewireIndex;
use App\Rules\FileExists;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Pest\Faker\faker;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    // cria fake logs de aplicação
    $this->log_files = ['laravel-2020-10-30.log', 'laravel.log'];

    $this->fake_disk = Storage::fake('application-log');

    $conten_1 = collect();
    $conten_2 = collect();

    foreach (range(1, 110) as $counter) {
        $conten_1->push(faker()->sentence());

        if ($counter % 2 === 1) {
            $conten_2->push(faker()->sentence());
        }
    }

    $this->fake_disk->put($this->log_files[0], $conten_1->join(PHP_EOL));
    $this->fake_disk->put($this->log_files[1], $conten_2->join(PHP_EOL));

    login('foo');
});

afterEach(function () {
    $this->fake_disk = Storage::fake('application-log');

    logout();
});

// Authorization
test('não é possível listar os logs da aplicação sem estar autenticado', function () {
    logout();

    get(route('administration.log.index'))
    ->assertRedirect(route('login'));
});

test('autenticado, mas sem permissão específica, não é possível executar a rota de listagem dos logs da aplicação', function () {
    get(route('administration.log.index'))
    ->assertForbidden();
});

test('não é possível renderizar o componente de listagem dos logs da aplicação sem permissão específica', function () {
    Livewire::test(LogLivewireIndex::class)->assertForbidden();
});

test('não é possível excluir o arquivo de log sem permissão específica', function () {
    grantPermission(PermissionType::LogViewAny->value);

    $this->fake_disk->assertExists($this->log_files);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('destroy')
    ->assertForbidden();
});

test('não é possível fazer o download do arquivo de log sem permissão específica', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('download')
    ->assertForbidden();
});

// Failure
test('se as valores forem inválidos na query string, eles serão definidas pelo sistema', function () {
    grantPermission(PermissionType::LogViewAny->value);

    // força dormir 1 segundos, para alterar o time no file system, pois travel
    // e testtime não alteram o tempo do fileserver, apenas em nível de php.
    sleep(1);
    // modifica o arquivo para ser o arquivo mais recente.
    $this->fake_disk->append($this->log_files[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'filename' => 'foo.log',
    ])
    ->assertSet('filename', $this->log_files[1]);
});

// Rules
test('não aceita paginação fora das opções oferecidas', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('per_page', 33) // valores possíveis: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('filename é obrigatório', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', '') // valores possíveis: 10/25/50/100
    ->assertHasErrors(['filename' => 'required']);
});

test('filename deve ser uma string', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', ['bar'])
    ->assertHasErrors(['filename' => 'string']);
});

test('filename deve respeitar o padrão de nome default do laravel para arquivos de log', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', 'foo.log')
    ->assertHasErrors(['filename' => 'regex']);
});

test('arquivo definido no filename deve exister no storage', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', 'laravel-1900-01-30.log')
    ->assertHasErrors(['filename' => FileExists::class]);
});

// Happy path
test('paginação retorna a quantidade de linhas do arquivo de log da aplicação esperada', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertCount('file_content', 10)
    ->set('per_page', 10)
    ->assertCount('file_content', 10)
    ->set('per_page', 25)
    ->assertCount('file_content', 25)
    ->set('per_page', 50)
    ->assertCount('file_content', 50)
    ->set('per_page', 100)
    ->assertCount('file_content', 100);
});

test('retorna a quantidade de arquivos de logs de aplicação esperada', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertCount('log_files', 2);
});

test('paginação cria as variáveis de sessão', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('arquivo de log default é o com a modificação mais recente', function () {
    grantPermission(PermissionType::LogViewAny->value);

    // força dormir 1 segundos, para alterar o time no file system, pois travel
    // e testtime não alteram o tempo do fileserver, apenas em nível de php.
    sleep(1);
    // modifica o arquivo para ser o arquivo mais recente.
    $this->fake_disk->append($this->log_files[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->assertSet('filename', $this->log_files[1]);
});

test('se as valores forem válidos na query string, eles serão utilizados', function () {
    grantPermission(PermissionType::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[0],
    ])
    ->assertSet('filename', $this->log_files[0]);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->assertSet('filename', $this->log_files[1]);
});

test('emite evento de feedback ao atualizar um perfil', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDelete->value);

    Livewire::test(LogLivewireIndex::class, [
        'filename' => $this->log_files[1],
    ])
    ->call('destroy')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('mesmo sem arquivo a ser exibido, o componente é carregado sem erros', function () {
    grantPermission(PermissionType::LogViewAny->value);

    $this->fake_disk->delete($this->log_files);

    $this->fake_disk->assertDirectoryEmpty('');

    Livewire::test(LogLivewireIndex::class)
    ->assertSet('filename', null)
    ->assertOk();
});

test('é possível listar os logs da aplicação com permissão específica', function () {
    grantPermission(PermissionType::LogViewAny->value);

    get(route('administration.log.index'))
    ->assertOk()
    ->assertSeeLivewire(LogLivewireIndex::class);
});

test('é possível excluir o arquivo de log com permissão específica', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDelete->value);

    $this->fake_disk->assertExists($this->log_files);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('destroy')
    ->assertOk();

    $this->fake_disk->assertMissing($this->log_files[0]);
    $this->fake_disk->assertExists($this->log_files[1]);
});

test('é possível fazer o download do arquivo de log com permissão específica', function () {
    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::LogDownload->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('filename', $this->log_files[0])
    ->call('download')
    ->assertFileDownloaded($this->log_files[0]);
});
