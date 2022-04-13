<?php

/**
 * @see https://pestphp.com/docs/
 */

use function App\maxSafeInteger;
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
