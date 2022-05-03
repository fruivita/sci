<?php

/**
 * @author Fábio Cassiano <fabiocassiano@jfes.jus.br>
 *
 * @link https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Storage;

test('possui permissão de escrita no storage de log de impressão', function () {
    $filename = 'this file can be deleted.txt';

    $disk = Storage::disk('print-log');

    $disk->put($filename, 'Foo Bar Baz');

    $disk->assertExists($filename);

    $disk->delete($filename);

    $disk->assertMissing($filename);
})->group('integration');
