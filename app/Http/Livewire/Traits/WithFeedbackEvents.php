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
     * Dispara um browser event para ser capturado por javascript detalhando
     * o resultado da solicitação do usuário.
     *
     * @param bool        $success se a solicitação do usuário foi executada
     * com sucesso.
     * @param string|null $message mensagem de retorno.
     * @param int         $timeout tempo de exibição da mensagem antes de ela
     *                    desaparecer em segundos.
     *
     * @return void
     */
    private function notify(bool $success, string $message = null, int $timeout = 3)
    {

        $feedback = ($success === true)
        ? FeedbackType::Success
        : FeedbackType::Error;

        $this->dispatchBrowserEvent('notify', [
            'type' => $feedback->value,
            'icon' => $feedback->icon(),
            'header' => $feedback->label(),
            'message' => $message,
            'timeout' => $timeout * 1000,
        ]);
    }
}
