<?php

/**
 * @see https://pestphp.com/docs/
 */

use function App\maxSafeInteger;
use function App\reportMaxDate;
use function App\reportMaxYear;
use function App\reportMinDate;
use function App\reportMinYear;
use function App\stringToArrayAssoc;

// Invalid
test('stringToArrayAssoc retorna null se valores inválidos forem informados', function ($keys, $delimiter, $string) {
    expect(stringToArrayAssoc($keys, $delimiter, $string))->toBeNull();
})->with([
    [
        ['nome', 'idade', 'nacionalidade', 'chave_em_excesso'], // qtd de chaves incompatível com a string
        ',',
        'foo,18,bar',
    ],
    [
        [], // chaves não informadas (array vazio)
        ',',
        'foo,18,bar',
    ],
    [
        ['nome', 'idade', 'nacionalidade'],
        ',',
        '', // string não informada (falso boleano)
    ],
    [
        ['nome', 'idade', 'nacionalidade'],
        '', // delimitador não informado (falso boleano)
        'foo,18,bar',
    ],
]);

// Happy path
test('maxSafeInteger retorna o valor do maior integer seguro, isto é, não sujeito a truncagem, para trabalhos com javascript', function () {
    expect(maxSafeInteger())->toBe(9007199254740991);
});

test('stringToArrayAssoc explode a string com base no delimitador e retorna um array associativo', function () {
    $keys = ['nome', 'idade', 'nacionalidade'];
    $string = 'foo,18,bar';
    $delimiter = ',';
    $expected = [
        'nome' => 'foo',
        'idade' => '18',
        'nacionalidade' => 'bar',
    ];

    expect(stringToArrayAssoc($keys, $delimiter, $string))->toMatchArray($expected);
});

test('reportMinYear retorna o ano mínimo dos relatórios', function () {
    expect(reportMinYear())->toBe((int) now()->subCentury()->format('Y'));
});

test('reportMaxYear retorna o ano máximo dos relatórios', function () {
    expect(reportMaxYear())->toBe((int) today()->format('Y'));
});

test('reportMinDate retorna a data mínima dos relatórios', function () {
    expect(reportMinDate()->format('d-m-Y'))->toBe(now()->subCentury()->format('d-m-Y'));
});

test('reportMaxDate retorna a data máxima dos relatórios', function () {
    expect(reportMaxDate()->format('d-m-Y'))->toBe(today()->format('d-m-Y'));
});
