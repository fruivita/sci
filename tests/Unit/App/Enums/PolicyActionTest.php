<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

test('Policy enum corretamente definidos', function () {
    expect(Policy::ViewAny->value)->toBe('viewAny')
    ->and(Policy::View->value)->toBe('view')
    ->and(Policy::Create->value)->toBe('create')
    ->and(Policy::Update->value)->toBe('update')
    ->and(Policy::Restore->value)->toBe('restore')
    ->and(Policy::Delete->value)->toBe('delete')
    ->and(Policy::ForceDelete->value)->toBe('forceDelete');
});
