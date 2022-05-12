<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Exceptions
test('throws an exception when trying to create duplicate permissions, that is, with the same ids or names', function () {
    expect(
        fn () => Permission::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Permission::factory(2)->create(['name' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create permission with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Permission::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(51), 'Data too long for column'],         // máximo 50 caracteres
    ['name', null,            'cannot be null'],                   // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

// Failures
test('atomicSaveWithRoles method rolls back on failure of permission update', function () {
    $permission_name = 'foo';
    $permission_description = 'bar';

    $permission = Permission::factory()->create([
        'name' => $permission_name,
        'description' => $permission_description,
    ]);

    $permission->name = 'new foo';
    $permission->description = 'new bar';

    // relationship with non-existent roles
    $saved = $permission->atomicSaveWithRoles([1, 2]);

    $permission->refresh()->load('roles');

    expect($saved)->toBeFalse()
    ->and($permission->name)->toBe($permission_name)
    ->and($permission->description)->toBe($permission_description)
    ->and($permission->roles)->toBeEmpty();
});

test('atomicSaveWithRoles method creates log on failed permission update', function () {
    Log::spy();

    $permission = Permission::factory()->create();

    // relationship with non-existent roles
    $permission->atomicSaveWithRoles([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(fn ($message) => $message === __('Permission update failed'))
    ->once();
});

// Happy path
test('create many permissions', function () {
    Permission::factory(30)->create();

    expect(Permission::count())->toBe(30);
});

test('optional permission fields are accepted', function () {
    Permission::factory()->create(['description' => null]);

    expect(Permission::count())->toBe(1);
});

test('permission fields at their maximum size are accepted', function () {
    Permission::factory()->create([
        'name' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Permission::count())->toBe(1);
});

test('one permission belong to many roles', function () {
    $permission = Permission::factory()
        ->has(Role::factory(3), 'roles')
        ->create();

    $permission->load('roles');

    expect($permission->roles)->toHaveCount(3);
});

test('atomicSaveWithRoles method saves the new attributes and creates relationship with the informed roles', function () {
    $permission_name = 'foo';
    $permission_description = 'bar';

    $permission = Permission::factory()->create([
        'name' => 'baz',
        'description' => 'foo bar baz',
    ]);

    Role::factory()->create(['id' => 1]);
    Role::factory()->create(['id' => 2]);
    Role::factory()->create(['id' => 3]);

    $permission->name = $permission_name;
    $permission->description = $permission_description;

    $saved = $permission->atomicSaveWithRoles([1, 3]);
    $permission->refresh()->load('roles');

    expect($saved)->toBeTrue()
    ->and($permission->name)->toBe($permission_name)
    ->and($permission->description)->toBe($permission_description)
    ->and($permission->roles->modelKeys())->toBe([1, 3]);
});

test('previous returns the correct previous record, even if it is the first', function () {
    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);

    expect($permission_2->previous()->first()->id)->toBe($permission_1->id)
    ->and($permission_1->previous()->first())->toBeNull();
});

test('next returns the correct back record even though it is the last', function () {
    $permission_1 = Permission::factory()->create(['id' => 1]);
    $permission_2 = Permission::factory()->create(['id' => 2]);

    expect($permission_1->next()->first()->id)->toBe($permission_2->id)
    ->and($permission_2->next()->first())->toBeNull();
});

test('returns the permissions using the defined default sort scope', function () {
    $first = 1;
    $second = 2;
    $third = 3;

    Permission::factory()->create(['id' => $third]);
    Permission::factory()->create(['id' => $first]);
    Permission::factory()->create(['id' => $second]);

    $permissions = Permission::defaultOrder()->get();

    expect($permissions->get(0)->id)->toBe($first)
    ->and($permissions->get(1)->id)->toBe($second)
    ->and($permissions->get(2)->id)->toBe($third);
});
