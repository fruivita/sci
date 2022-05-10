<?php

namespace App\Enums;

/*
 * Tipos de importação que a aplicação pode fazer.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum ImportationType: string
{
    case Corporate = 'corporate';
    case PrintLog = 'print_log';
    /**
     * Nome para exibição do tipo de importação.
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
     * Nome para exibição da queue que será usada para o tipo de importação.
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
     * Todos os valores possíveis para o value do enum.
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
