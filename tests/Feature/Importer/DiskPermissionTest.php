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

test('can read the corporate file', function () {
    $full_path = config('company.corporate_file');

    expect((new \SplFileInfo($full_path))->isReadable())->toBeTrue();
})->group('integration');
