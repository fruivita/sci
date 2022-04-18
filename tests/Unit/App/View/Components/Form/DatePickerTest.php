<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\View\Components\Form\DatePicker;

// Invalid
test('data mínima em formato inválido, usa a data de 100 anos atrás', function () {
    $config = (new DatePicker(min_date: '15.02.2020'))->getFlatpickrConfiguration();

    expect($config['minDate'])->toBe(now()->subCentury()->format('d-m-Y'));
});

test('data máxima em formato inválido, usa a data de hoje', function () {
    $config = (new DatePicker(max_date: '15.02.2020'))->getFlatpickrConfiguration();

    expect($config['maxDate'])->toBe(now()->format('d-m-Y'));
});

// Happy path
test('define a data mínima', function () {
    $config = (new DatePicker(min_date: '15-02-2020'))->getFlatpickrConfiguration();

    expect($config['minDate'])->toBe('15-02-2020');
});

test('define a data máxima', function () {
    $config = (new DatePicker(max_date: '15-02-2020'))->getFlatpickrConfiguration();

    expect($config['maxDate'])->toBe('15-02-2020');
});

test('se não informar os parâmetros, utilizada-se os valores default', function () {
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
