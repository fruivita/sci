<?php

namespace App\Enums;

/*
 * List of queues present in the application.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum QueueType: string
{
    case Corporate = 'corporate';
    case PrintLog = 'print_log';
}
