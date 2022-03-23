<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;

test('CheckboxAction enum corretamente definidos', function () {
    expect(CheckboxAction::CheckAll->value)->toBe('check_all')
    ->and(CheckboxAction::UncheckAll->value)->toBe('uncheck_all')
    ->and(CheckboxAction::CheckAllPage->value)->toBe('check_all_page')
    ->and(CheckboxAction::UncheckAllPage->value)->toBe('uncheck_all_page');
});

test('CheckboxAction enum label definido', function () {
    expect(CheckboxAction::CheckAll->label())->toBe(__('Check all'))
    ->and(CheckboxAction::UncheckAll->label())->toBe(__('Uncheck all'))
    ->and(CheckboxAction::CheckAllPage->label())->toBe(__('Check all on page'))
    ->and(CheckboxAction::UncheckAllPage->label())->toBe(__('Uncheck all on page'));
});

test('CheckboxAction enum values definido', function () {
    expect(CheckboxAction::values()->toArray())->toBe(['check_all', 'uncheck_all', 'check_all_page', 'uncheck_all_page']);
});
