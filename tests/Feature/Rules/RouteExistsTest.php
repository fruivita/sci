<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\RouteExists;

test('validates if the route exists in the application, that is, if it is a valid route', function ($value, $expect) {
    $rule = new RouteExists();

    expect($rule->passes('app_route_name', $value))->toBe($expect);
})->with([
    ['foo.bar', false], // invalid, non-existent route
    ['administration.log.index', true],
]);
