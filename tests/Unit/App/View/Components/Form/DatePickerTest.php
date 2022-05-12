<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\View\Components\Form\DatePicker;

// Invalid
test('if minimum date is in invalid format, use date from 100 years ago', function () {
    $config = (new DatePicker(min_date: '15.02.2020'))->getFlatpickrConfiguration();

    expect($config['minDate'])->toBe(now()->subCentury()->format('d-m-Y'));
});

test("if the maximum date is in invalid format, use today's date", function () {
    $config = (new DatePicker(max_date: '15.02.2020'))->getFlatpickrConfiguration();

    expect($config['maxDate'])->toBe(now()->format('d-m-Y'));
});

// Happy path
test('sets the minimum date', function () {
    $config = (new DatePicker(min_date: '15-02-2020'))->getFlatpickrConfiguration();

    expect($config['minDate'])->toBe('15-02-2020');
});

test('sets the maximum date', function () {
    $config = (new DatePicker(max_date: '15-02-2020'))->getFlatpickrConfiguration();

    expect($config['maxDate'])->toBe('15-02-2020');
});

test('if do not inform the parameters, the default values are used', function () {
    $config = (new DatePicker())->getFlatpickrConfiguration();

    expect($config)->toBe([
        'allowInput' => true,
        'dateFormat' => 'd-m-Y',
        'disableMobile' => true,
        'locale' => 'pt',
        'minDate' => now()->subCentury()->format('d-m-Y'),
        'maxDate' => now()->format('d-m-Y'),
    ]);
});
