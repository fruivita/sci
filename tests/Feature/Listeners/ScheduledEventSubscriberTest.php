<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use function Spatie\PestPluginTestTime\testTime;

// Happy path
test('logs the start and end of the task dispatched by the schedule', function () {
    Bus::fake();
    Log::spy();

    testTime()->freeze('2020-10-20 01:00:00');

    $this->artisan('schedule:run');

    Log::shouldHaveReceived('log')
    ->withArgs(fn ($level, $message) => $level === 'notice' && $message === 'ScheduledTaskStarting')
    ->once();
    Log::shouldHaveReceived('log')
    ->withArgs(fn ($level, $message) => $level === 'notice' && $message === 'ScheduledTaskFinished')
    ->once();
});
