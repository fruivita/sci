<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

use function Spatie\PestPluginTestTime\testTime;

// Happy path
test('registra em log o ínicio e o fim da tarefa dispachada pelo schedule', function () {
    Bus::fake();
    Log::spy();

    testTime()->freeze('2020-10-20 01:00:00');

    $this->artisan('schedule:run');

    Log::shouldHaveReceived('log')
    ->withArgs(
        function ($level, $message) {
            return $level === 'notice' && $message === 'ScheduledTaskStarting';
        }
    )->once()
    ->withArgs(
        function ($level, $message) {
            return $level === 'notice' && $message === 'ScheduledTaskFinished';
        }
    )->once();
});
