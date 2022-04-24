<?php

/**
 * @see https://pestphp.com/docs/
 */


use App\Models\Configuration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exceção ao tentar cadastrar configuração com campo inválido', function ($field, $value, $message) {
    expect(
        fn () => Configuration::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['superadmin', Str::random(21), 'Data too long for column'], // máximo 21 caracteres
    ['superadmin', null,            'cannot be null'],           // obrigatório
]);

// Happy path
test('super admin em seu tamanho máximo é aceito', function () {
    Configuration::factory()->create(['superadmin' => Str::random(20)]);

    expect(Configuration::count())->toBe(1);
});

test('id da configuração da aplicação está definido', function () {
    expect(Configuration::MAIN)->toBe(101);
});
