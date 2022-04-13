<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Importer\PrintImporter;
use App\Jobs\ImportCorporateStructure;
use App\Models\Client;
use App\Models\User;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function(){
    $this->seed(RoleSeeder::class);
});

test('make retorna o objeto da classe', function () {
    expect(PrintImporter::make())->toBeInstanceOf(PrintImporter::class);
});

test('todos os campos da impressão precisam estar presentes, mesmo que vazios', function () {
    // sem a delimitar o último parâmetro (qtd de cópias), portanto, campos incompletos
    $print = 'server.dominio.org.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1';

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(0)
    ->and(Server::count())->toBe(0)
    ->and(Client::count())->toBe(0)
    ->and(Printer::count())->toBe(0)
    ->and(User::count())->toBe(0);
});

test('cria o log se o servidor de impressão for inválido na string de impressão', function ($server) {
    $print = "{$server}╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(0);
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    Str::random(256), // máximo 255 caracteres
    null,             // obrigatório
]);

test('cria o log se o cliente for inválido na string de impressão', function ($client) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡╡╡{$client}╡IMP-123╡2567217╡1╡1";
    Log::spy();
    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    Str::random(256), // máximo 255 caracteres
    null,             // obrigatório
]);

test('cria o log se o usuário for inválido na string de impressão', function ($username) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡{$username}╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    Str::random(21), // máximo 21 caracteres
    null,             // campo obrigatório
]);

test('cria o log se a impressora for inválida na string de impressão', function ($printer) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡╡╡CPU-10000╡{$printer}╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    Str::random(256), // máximo 255 caracteres
    null,             // campo obrigatório
]);

test('cria o log se a lotação for inválida na string de impressão', function ($department) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡{$department}╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    'foo', // não conversível em inteiro
    10,    // inexistente
]);

test('cria o log se a data da impressão for inválida na string de impressão', function ($date) {
    $print = "server.dominio.gov.br╡{$date}╡07:35:35╡documento de teste.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    '31/02/2020', // data inexistente
    '28-02-2020', // deve ser no formato dd/mm/yyyy
    null,         // obrigatório
]);

test('cria o log se a hora da impressão for inválida na string de impressão', function ($time) {
    $print = "server.dominio.gov.br╡01/06/2020╡{$time}╡documento de teste.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    '23:61:59', // hora inexistente
    '2:59:59',  // deve ser no formato hh:mm:ss
    null,       // obrigatório
]);

test('cria o log se o nome do arquivo impresso for inválido na string de impressão', function ($filename) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡{$filename}╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    Str::random(261), // máximo 260 caracteres
]);

test('o nome do arquivo é opcional', function () {
    $filename = null;

    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡{$filename}╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1";

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
});

test('cria o log se o número de páginas for inválido na string de impressão', function ($pages) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡{$pages}╡1";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    'foo', // não conversível em inteiro
    null,  // obrigatório
]);

test('cria o log se o número de cópias for inválido na string de impressão', function ($copies) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡5╡{$copies}";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    'foo', // não conversível em inteiro
    null,  // obrigatório
]);

test('cria o log se o tamanho do arquivo for inválido na string de impressão', function ($file_size) {
    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡arquivo.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡{$file_size}╡5╡2";
    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::get())->toBeEmpty();
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'warning');
})->with([
    'foo', // não conversível em inteiro
]);

test('o tamanho do arquivo é opcional', function () {
    $file_size = null;

    $print = "server.dominio.gov.br╡01/06/2020╡07:35:35╡documento.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡{$file_size}╡1╡1";

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
});

test('transação faz roolback em caso de exception na persistência da impressão', function () {
    // Note que duas impressões com a mesma data, hora, cliente, impressora, usuário e servidor são considerais iguais.
    // Nesse caso, os dados da segunda impressão não devem existir no banco de dados devido ao roolback.
    $print_1 = 'server1.dominio.gov.br╡01/06/2020╡07:35:35╡documento1.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    $print_2 = 'server1.dominio.gov.br╡01/06/2020╡07:35:35╡documento2.pdf╡aduser╡2022╡╡╡CPU-10000╡IMP-123╡5567217╡2╡3';

    PrintImporter::make()->import($print_1);
    PrintImporter::make()->import($print_2);

    expect(Printing::count())->toBe(1)
    ->and(Server::count())->toBe(1)
    ->and(Client::count())->toBe(1)
    ->and(Printer::count())->toBe(1)
    ->and(User::count())->toBe(1)
    ->and(Printing::where('filename', 'documento2.pdf')->first())->toBeNull();
});

test('cria o log se houver exception durante a persistência da impressão', function () {
    // as impressões a seguir são consideradas iguais
    $print_1 = 'server1.dominio.gov.br╡01/06/2020╡07:35:35╡documento1.pdf╡aduser╡2021╡╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    $print_2 = 'server1.dominio.gov.br╡01/06/2020╡07:35:35╡documento2.pdf╡aduser╡2022╡╡╡CPU-10000╡IMP-123╡5567217╡2╡3';
    Log::spy();

    PrintImporter::make()->import($print_1);

    PrintImporter::make()->import($print_2);

    expect(Printing::count())->toBe(1);
    Log::shouldHaveReceived('log')->once()->withArgs(fn($level) => $level === 'critical');
});

test('se a lotação existir, não acusará erro de validação', function () {
    $print = 'server.dominio.gov.br╡01/06/2020╡07:35:35╡documento de teste.pdf╡aduser╡2021╡2╡╡CPU-10000╡IMP-123╡2567217╡1╡1';
    ImportCorporateStructure::dispatchSync();

    Log::spy();

    PrintImporter::make()->import($print);

    expect(Printing::count())->toBe(1);
    Log::shouldNotHaveReceived('log');
});

test('importa uma impressão', function () {
    $server = 'server.dominio.gov.br';
    $client = 'CPU-10000';
    $username = 'aduser';
    $printer = 'IMP-123';
    $filename = 'documento de teste.pdf';

    $print = "{$server}╡01/06/2020╡07:35:35╡{$filename}╡{$username}╡2021╡╡╡{$client}╡{$printer}╡2567217╡4╡7";

    PrintImporter::make()->import($print);

    $record = Printing::first();

    expect(Server::where('name', $server)->count())->toBe(1)
    ->and(Client::where('name', $client)->count())->toBe(1)
    ->and(Printer::where('name', $printer)->count())->toBe(1)
    ->and(User::where('username', $username)->count())->toBe(1)
    ->and($record->id)->not->toBeNull()
    ->and($record->date)->toBe('2020-06-01')
    ->and($record->time)->toBe('07:35:35')
    ->and($record->filename)->toBe($filename)
    ->and($record->file_size)->toBe(2567217)
    ->and($record->pages)->toBe(4)
    ->and($record->copies)->toBe(7);
});
