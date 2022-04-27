<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\FileExists;
use Illuminate\Support\Facades\Storage;

test('valida se o arquivo existe ou não no storage', function () {
    $fake_disk = Storage::fake('log-impressao');

    $rule = new FileExists('log-impressao');

    expect($rule->passes('file', 'foo.txt'))->toBeFalse();

    $fake_disk->put('foo.txt', 'content');

    expect($rule->passes('file', 'foo.txt'))->toBeTrue();

    $fake_disk = Storage::fake('log-impressao');
});
