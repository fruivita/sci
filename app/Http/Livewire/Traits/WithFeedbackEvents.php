<?php

namespace App\Http\Livewire\Traits;

use App\Enums\FeedbackType;

/**
 * Trait designed to issue feedback events to the user.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://laravel-livewire.com/docs/2.x/events
 */
trait WithFeedbackEvents
{
    /**
     * Emits feedback event for the component itself to decide where best to
     * display it.
     *
     * The informed message will be displayed or, if not informed, the default
     * message will be used.
     *
     * @param bool        $success if the command was executed successfully
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
     * Fires a browser event to be captured by javascript detailing the result
     * of the user's request.
     *
     * @param bool        $success whether the user's request was executed
     *                             successfully
     * @param string|null $message return message
     * @param int         $timeout message display time before it disappears in
     *                             seconds
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
