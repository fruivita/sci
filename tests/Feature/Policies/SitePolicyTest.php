<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SitePolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('user without permission cannot list sites', function () {
    expect((new SitePolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a site', function () {
    expect((new SitePolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a site', function () {
    expect((new SitePolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a site', function () {
    expect((new SitePolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a site', function () {
    expect((new SitePolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('Site listing permission is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually refresh a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list the sites', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    expect((new SitePolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a website', function () {
    grantPermission(PermissionType::SiteView->value);

    expect((new SitePolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a site', function () {
    grantPermission(PermissionType::SiteCreate->value);

    expect((new SitePolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a site', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    expect((new SitePolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a site', function () {
    grantPermission(PermissionType::SiteDelete->value);

    expect((new SitePolicy())->delete($this->user))->toBeTrue();
});
