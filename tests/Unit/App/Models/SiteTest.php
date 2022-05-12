<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Server;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create sites in duplicate, that is, with the same names', function () {
    expect(
        fn () => Site::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create site with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Site::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['name', null,             'cannot be null'],           // required
]);

// Failure
test('atomicSaveWithServers method rolls back on failure of site update', function () {
    $site = Site::factory()->create([
        'name' => 'foo',
    ]);

    $site->name = 'new foo';

    // relationship with non-existent servers
    $saved = $site->atomicSaveWithServers([1, 2]);

    $site->refresh()->load('servers');

    expect($saved)->toBeFalse()
    ->and($site->name)->toBe('foo')
    ->and($site->servers)->toBeEmpty();
});

test('atomicSaveWithServers method creates log if site update fails', function () {
    Log::spy();

    $site = Site::factory()->create();

    // relationship with non-existent servers
    $site->atomicSaveWithServers([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(fn ($message) => $message === __('Site update failed'))
    ->once();
});

// Happy path
test('create many sites', function () {
    Site::factory(30)->create();

    expect(Site::count())->toBe(30);
});

test('site name at its maximum size is accepted', function () {
    Site::factory()->create(['name' => Str::random(255)]);

    expect(Site::count())->toBe(1);
});

test('one site is controlled by many servers', function () {
    Site::factory()
        ->has(Server::factory(3), 'servers')
        ->create();

    $sites = Site::with('servers')->first();

    expect($sites->servers)->toHaveCount(3);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'foo']);

    expect($site_2->previous()->first()->id)->toBe($site_1->id)
    ->and($site_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $site_1 = Site::factory()->create(['name' => 'bar']);
    $site_2 = Site::factory()->create(['name' => 'foo']);

    expect($site_1->next()->first()->id)->toBe($site_2->id)
    ->and($site_2->next()->first())->toBeNull();
});

test('returns the sites using the default sort scope defined', function () {
    $first = 'bar';
    $second = 'baz';
    $third = 'foo';

    Site::factory()->create(['name' => $third]);
    Site::factory()->create(['name' => $first]);
    Site::factory()->create(['name' => $second]);

    $sites = Site::defaultOrder()->get();

    expect($sites->get(0)->name)->toBe($first)
    ->and($sites->get(1)->name)->toBe($second)
    ->and($sites->get(2)->name)->toBe($third);
});

test('atomicSaveWithServers method saves the new attributes and creates a relationship with the informed servers', function () {
    $site = Site::factory()->create([
        'name' => 'foo',
    ]);

    Server::factory()->create(['id' => 1]);
    Server::factory()->create(['id' => 2]);
    Server::factory()->create(['id' => 3]);

    $site->name = 'new foo';

    $saved = $site->atomicSaveWithServers([1, 3]);
    $site->refresh()->load('servers');

    expect($saved)->toBeTrue()
    ->and($site->name)->toBe('new foo')
    ->and($site->servers->modelKeys())->toBe([1, 3]);
});
