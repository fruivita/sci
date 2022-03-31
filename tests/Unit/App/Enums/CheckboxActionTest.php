<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;

// Happy path
test('CheckboxAction enum corretamente definidos', function () {
    expect(CheckboxAction::CheckAll->value)->toBe('check-all')
    ->and(CheckboxAction::UncheckAll->value)->toBe('uncheck-all')
    ->and(CheckboxAction::CheckAllPage->value)->toBe('check-all-page')
    ->and(CheckboxAction::UncheckAllPage->value)->toBe('uncheck-all-page');
});

test('CheckboxAction enum label definido', function () {
    expect(CheckboxAction::CheckAll->label())->toBe(__('Check all'))
    ->and(CheckboxAction::UncheckAll->label())->toBe(__('Uncheck all'))
    ->and(CheckboxAction::CheckAllPage->label())->toBe(__('Check all on page'))
    ->and(CheckboxAction::UncheckAllPage->label())->toBe(__('Uncheck all on page'));
});

test('CheckboxAction enum values definido', function () {
    expect(CheckboxAction::values()->toArray())->toBe(['check-all', 'uncheck-all', 'check-all-page', 'uncheck-all-page']);
});
