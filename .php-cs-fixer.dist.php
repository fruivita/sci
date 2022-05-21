<?php

/**
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 */
$finder = \PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . DIRECTORY_SEPARATOR . 'app',
        __DIR__ . DIRECTORY_SEPARATOR . 'config',
        __DIR__ . DIRECTORY_SEPARATOR . 'database',
        __DIR__ . DIRECTORY_SEPARATOR . 'lang',
        __DIR__ . DIRECTORY_SEPARATOR . 'resources',
        __DIR__ . DIRECTORY_SEPARATOR . 'routes',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests',
    ])
    ->append(['.php-cs-fixer.dist.php']);

$rules = [
    '@Symfony' => true,
    'yoda_style' => false,
    'concat_space' => ['spacing' => 'one'],
    'new_with_braces' => ['anonymous_class' => false],
    'not_operator_with_successor_space' => true,
    'blank_line_before_statement' => [
        'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
    ],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => true,
    ],
    'phpdoc_line_span' => true,
    'no_superfluous_phpdoc_tags' => false,
    'phpdoc_order' => true,
    'phpdoc_types_order' => [
        'null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha',
    ],
    'phpdoc_var_annotation_correct_order' => true,
    'ordered_traits' => true,
];

return (new \PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules($rules)
    ->setFinder($finder);
