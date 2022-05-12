<?php

namespace App\Enums;

/*
 * Types of import that the application can do.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum ImportationType: string
{
    case Corporate = 'corporate';
    case PrintLog = 'print_log';
    /**
     * Display name of the import type.
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            ImportationType::Corporate => __('Corporate structure'),
            ImportationType::PrintLog => __('Print log')
        };
    }

    /**
     * Display name of the queue that will be used for the import type.
     *
     * @return string
     */
    public function queue()
    {
        return match ($this) {
            ImportationType::Corporate => QueueType::Corporate->value,
            ImportationType::PrintLog => QueueType::PrintLog->value
        };
    }

    /**
     * All possible values for the enum value.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function values()
    {
        return
        collect(QueueType::cases())
        ->transform(function ($type) {
            return $type->value;
        });
    }
}
