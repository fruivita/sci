<?php

/**
 * @see https://pestphp.com/docs/
 */

use function App\maxSafeInteger;
use function App\reportMaxDate;
use function App\reportMaxYear;
use function App\reportMinDate;
use function App\reportMinYear;
use function App\stringToArrayAssoc;

// Invalid
test('stringToArrayAssoc returns null if invalid values are given', function ($keys, $delimiter, $string) {
    expect(stringToArrayAssoc($keys, $delimiter, $string))->toBeNull();
})->with([
    [
        ['name', 'age', 'nationality', 'excess_key'], //qty of keys incompatible with string
        ',',
        'foo,18,bar',
    ],
    [
        [], // unspecified keys (empty array)
        ',',
        'foo,18,bar',
    ],
    [
        ['name', 'age', 'nationality'],
        ',',
        '', // unreported string (false boolean)
    ],
    [
        ['name', 'age', 'nationality'],
        '', // delimiter not informed (false boolean)
        'foo,18,bar',
    ],
]);

// Happy path
test('maxSafeInteger returns the value of the largest safe integer, i.e. not subject to truncation, for javascript work', function () {
    expect(maxSafeInteger())->toBe(9007199254740991);
});

test('stringToArrayAssoc explodes the string based on the delimiter and returns an associative array', function () {
    $keys = ['name', 'age', 'nationality'];
    $string = 'foo,18,bar';
    $delimiter = ',';
    $expected = [
        'name' => 'foo',
        'age' => '18',
        'nationality' => 'bar',
    ];

    expect(stringToArrayAssoc($keys, $delimiter, $string))->toMatchArray($expected);
});

test('reportMinYear returns the minimum year of the reports', function () {
    expect(reportMinYear())->toBe((int) now()->subCentury()->format('Y'));
});

test('reportMaxYear returns the maximum year of the reports', function () {
    expect(reportMaxYear())->toBe((int) today()->format('Y'));
});

test('reportMinDate returns the minimum date of the reports', function () {
    expect(reportMinDate()->format('d-m-Y'))->toBe(now()->subCentury()->format('d-m-Y'));
});

test('reportMaxDate returns the maximum date of the reports', function () {
    expect(reportMaxDate()->format('d-m-Y'))->toBe(today()->format('d-m-Y'));
});
