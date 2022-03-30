<?php

namespace App\Enums;

/*
 * Tipos de feedbacks dados aos usuários.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum FeedbackType: string
{
    case Success = 'success';
    case Error = 'error';
}
