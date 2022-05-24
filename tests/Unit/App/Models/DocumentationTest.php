<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create documentation in duplicate, that is, with equal routes', function () {
    expect(
        fn () => Documentation::factory(2)->create(['app_route_name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create documentation with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Documentation::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['app_route_name', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['app_route_name', null,             'cannot be null'],           // required
    ['doc_link',       Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

// Happy path
test('create many documentation', function () {
    Documentation::factory(30)->create();

    expect(Documentation::count())->toBe(30);
});

test('Documentation fields in their maximum size are accepted', function () {
    Documentation::factory()->create([
        'app_route_name' => Str::random(255),
        'doc_link' => Str::random(255),
    ]);

    expect(Documentation::count())->toBe(1);
});

test('optional fields are set', function () {
    Documentation::factory()->create(['doc_link' => null]);

    expect(Documentation::count())->toBe(1);
});

test('returns the documentations using the defined default sort scope', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Documentation::factory()->create(['app_route_name' => $third]);
    Documentation::factory()->create(['app_route_name' => $first]);
    Documentation::factory()->create(['app_route_name' => $second]);

    $docs = Documentation::defaultOrder()->get();

    expect($docs->get(0)->app_route_name)->toBe($first)
    ->and($docs->get(1)->app_route_name)->toBe($second)
    ->and($docs->get(2)->app_route_name)->toBe($third);
});
