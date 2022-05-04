<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledBackgroundTaskFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/events
 * @see https://laravel.com/docs/9.x/scheduling#events
 */
class ScheduledEventSubscriber
{
    /**
     * Handle Scheduled Task Starting events.
     *
     * @return void
     */
    public function handleScheduledTaskStarting(ScheduledTaskStarting $event)
    {
        $this->log('notice', 'ScheduledTaskStarting', [
            'expression' => $event->task->expression,
            'description' => $event->task->description
        ]);
    }

    /**
     * Handle Scheduled Task Finished events.
     *
     * @return void
     */
    public function handleScheduledTaskFinished(ScheduledTaskFinished $event)
    {
        $this->log('notice', 'ScheduledTaskFinished', [
            'expression' => $event->task->expression,
            'description' => $event->task->description
        ]);
    }

    /**
     * Handle Scheduled Task Failed events.
     *
     * @return void
     */
    public function handleScheduledTaskFailed(ScheduledTaskFailed $event)
    {
        $this->log('critical', 'ScheduledTaskFailed', [
            'expression' => $event->task->expression,
            'description' => $event->task->description,
            'exception' => $event->exception
        ]);
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            ScheduledTaskStarting::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskStarting']
        );

        $events->listen(
            ScheduledTaskFinished::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskFinished']
        );

        $events->listen(
            ScheduledTaskFailed::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskFailed']
        );
    }

    /**
     * Logs with an arbitrary level.
     *
     * The message MUST be a string or object implementing __toString().
     *
     * The message MAY contain placeholders in the form: {foo} where foo
     * will be replaced by the context data in key "foo".
     *
     * The context array can contain arbitrary data, the only assumption that
     * can be made by implementors is that if an Exception instance is given
     * to produce a stack trace, it MUST be in a key named "exception".
     *
     * @param string               $level   nível do log
     * @param string|\Stringable   $message sobre o ocorrido
     * @param array<string, mixed> $context dados de contexto
     *
     * @return void
     *
     * @see https://www.php-fig.org/psr/psr-3/
     * @see https://www.php.net/manual/en/function.array-merge.php
     */
    private function log(string $level, string|\Stringable $message, array $context = [])
    {
        Log::log($level, $message, $context);
    }
}
