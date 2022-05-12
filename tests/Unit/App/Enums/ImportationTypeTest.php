<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\ImportationType;

test('ImportationType enum correctly defined', function () {
    expect(ImportationType::Corporate->value)->toBe('corporate')
    ->and(ImportationType::PrintLog->value)->toBe('print_log');
});

test('ImportationType enum values defined', function () {
    expect(ImportationType::values()->toArray())->toBe(['corporate', 'print_log']);
});

test('ImportationType enum label defined', function () {
    expect(ImportationType::Corporate->label())->toBe(__('Corporate structure'))
    ->and(ImportationType::PrintLog->label())->toBe(__('Print log'));
});

test('ImportationType enum queue defined', function () {
    expect(ImportationType::Corporate->queue())->toBe('corporate')
    ->and(ImportationType::PrintLog->queue())->toBe(__('print_log'));
});
