<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\QueueType;

test('QueueType enum correctly defined', function () {
    expect(QueueType::Corporate->value)->toBe('corporate')
    ->and(QueueType::PrintLog->value)->toBe('print_log');
});
