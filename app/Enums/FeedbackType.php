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
    /**
     * Nome para exibição do tipo de mensagem.
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
     * Icone svg para cada tipo de mensagem.
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
