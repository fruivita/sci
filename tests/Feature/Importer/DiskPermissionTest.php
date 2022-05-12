<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Storage;

test('has write permission to print log storage', function () {
    $filename = 'this file can be deleted.txt';

    $disk = Storage::disk('print-log');

    $disk->put($filename, 'Foo Bar Baz');

    $disk->assertExists($filename);

    $disk->delete($filename);

    $disk->assertMissing($filename);
})->group('integration');
