<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Configuration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create configuration with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Configuration::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['superadmin', Str::random(21), 'Data too long for column'], // maximum 21characters
    ['superadmin', null,            'cannot be null'],           // required
]);

// Happy path
test('super admin at its maximum size is accepted', function () {
    Configuration::factory()->create(['superadmin' => Str::random(20)]);

    expect(Configuration::count())->toBe(1);
});

test('application configuration id is set', function () {
    expect(Configuration::MAIN)->toBe(101);
});
