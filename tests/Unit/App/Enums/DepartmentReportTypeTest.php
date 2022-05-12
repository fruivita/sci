<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\DepartmentReportType;

test('DepartmentReportType enum correctly defined', function () {
    expect(DepartmentReportType::Institutional->value)->toBe('institutional')
    ->and(DepartmentReportType::Managerial->value)->toBe('managerial')
    ->and(DepartmentReportType::Department->value)->toBe('department');
});

test('DepartmentReportType enum label defined', function () {
    expect(DepartmentReportType::Institutional->label())->toBe(__('Institutional'))
    ->and(DepartmentReportType::Managerial->label())->toBe(__('Managerial'))
    ->and(DepartmentReportType::Department->label())->toBe(__('Department'));
});

test('DepartmentReportType enum values defined', function () {
    expect(DepartmentReportType::values()->toArray())->toBe(['institutional', 'managerial', 'department']);
});
