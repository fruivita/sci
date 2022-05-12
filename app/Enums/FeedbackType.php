<?php

namespace App\Enums;

/*
 * Types of feedback given to users.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum FeedbackType: string
{
    case Success = 'success';
    case Error = 'error';
    /**
     * Display name of the message type.
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            FeedbackType::Error => __('Error!'),
            FeedbackType::Success => __('Success!')
        };
    }

    /**
     * Icon svg for each message type.
     *
     * @return string
     */
    public function icon()
    {
        return match ($this) {
            FeedbackType::Error => svg('emoji-frown')->toHtml(),
            FeedbackType::Success => svg('emoji-smile')->toHtml(),
        };
    }
}
