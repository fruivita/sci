<?php

namespace App\Enums;

/*
 * Lista de Queues presentes da aplicação.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum QueueType: string
{
    case Corporate = 'corporate';
    case PrintLog = 'print_log';
}
