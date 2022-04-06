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

test('FeedbackType enum label definido', function () {
    expect(FeedbackType::Success->label())->toBe(__('Success!'))
    ->and(FeedbackType::Error->label())->toBe(__('Error!'));
});

test('FeedbackType enum icons definido', function () {
    expect(FeedbackType::Success->icon())->toContain('emoji-smile', '<svg class="icon"', '</svg>')
    ->and(FeedbackType::Error->icon())->toContain('emoji-frown', '<svg class="icon"', '</svg>');
});
