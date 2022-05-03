<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\App;
use function Pest\Laravel\get;

test('a tarja do ambiente é exibida quando não se está em produção', function () {
    get(route('login'))
    ->assertSee(__(str()->ucfirst(App::environment())));
});

test('a tarja do ambiente não é exibida quando se está em produção', function () {
    App::shouldReceive('environment')
    ->andReturn('production');

    get(route('login'))
    ->assertDontSee(__(str()->ucfirst(App::environment())));
});
