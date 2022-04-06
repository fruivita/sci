<?php

namespace App\Http\Livewire\Traits;

use App\Enums\FeedbackType;

/**
 * Trait idealizada para emissão de eventos de feedback ao usuário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithFeedbackEvents
{
    /**
     * Emite evento sobre o resultado de operações do tipo save.
     *
     * @param bool $success se o comando foi executao com sucesso
     *
     * @return void
     */
    private function emitSaveInlineFeebackSelf(bool $success)
    {
        if ($success === true) {
            $msg = FeedbackType::Success->label();
            $feedback = FeedbackType::Success;
        } else {
            $msg = FeedbackType::Error->label();
            $feedback = FeedbackType::Error;
        }

        $this->emitSelf('feedback', $msg, $feedback);
    }

    /**
     * Emite evento de feedback para ser exibido no componente flash com a
     * mensagem informada.
     *
     * @param FeedbackType $feedback_type
     * @param string $message
     *
     * @return void
     */
    private function flash(FeedbackType $feedback_type, string $message)
    {
        $this->emitTo('flash', 'showFlash', [
            'type' => $feedback_type->value,
            'message' => $message,
        ]);
    }
}
