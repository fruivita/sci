<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\App;
use function Pest\Laravel\get;

test('environment stripe is displayed when not in production', function () {
    get(route('login'))
    ->assertSee(__(str()->ucfirst(App::environment())));
});

test('environment stripe is not displayed when in production', function () {
    App::shouldReceive('environment')
    ->andReturn('production');

    get(route('login'))
    ->assertDontSee(__(str()->ucfirst(App::environment())));
});
