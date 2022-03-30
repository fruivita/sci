<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;

// Happy path
test('FeedbackType enum corretamente definidos', function () {
    expect(FeedbackType::Success->value)->toBe('success')
    ->and(FeedbackType::Error->value)->toBe('error');
});
