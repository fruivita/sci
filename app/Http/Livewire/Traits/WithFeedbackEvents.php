<?php

namespace App\Http\Livewire\Traits;

use App\Enums\FeedbackType;

/**
 * Trait idealizada para emissão de eventos de feedback ao usuário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://laravel-livewire.com/docs/2.x/events
 */
trait WithFeedbackEvents
{
    /**
     * Emite evento de feedback para o próprio componente decidir onde melhor
     * será exibi-lo.
     *
     * A mensagem informada será exibida ou, se não informada, será utilizada a
     * mensagem padrão.
     *
     * @param bool        $success se o comando foi executao com sucesso
     * @param string|null $message
     *
     * @return void
     */
    private function flashSelf(bool $success, string $message = null)
    {
        if ($success === true) {
            $feedback = FeedbackType::Success;
            $msg = $message ?? FeedbackType::Success->label();
        } else {
            $feedback = FeedbackType::Error;
            $msg = $message ?? FeedbackType::Error->label();
        }

        $this->emitSelf('feedback', $feedback, $msg);
    }

    /**
     * Emite evento de feedback para ser exibido no componente flash com a
     * mensagem informada.
     *
     * A mensagem informada será exibida ou, se não informada, será utilizada a
     * mensagem padrão.
     *
     * @param bool        $success se o comando foi executao com sucesso
     * @param string|null $message
     *
     * @return void
     */
    private function flash(bool $success, string $message = null)
    {
        if ($success === true) {
            $feedback = FeedbackType::Success;
            $msg = $message ?? FeedbackType::Success->label();
        } else {
            $feedback = FeedbackType::Error;
            $msg = $message ?? FeedbackType::Error->label();
        }

        $this->emitTo('flash', 'showFlash', $feedback->value, $msg);
    }
}
