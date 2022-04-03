<?php

/**
 * @see https://pestphp.com/docs/
 */

use function App\maxSafeInteger;

// Happy path
test('maxSafeInteger retorna o valor do maior integer seguro, isto é, não sujeito a truncagem, para trabalhos com javascript', function () {
    expect(maxSafeInteger())->toBe(9007199254740991);
});
