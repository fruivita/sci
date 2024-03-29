<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportCorporateStructure;
use App\Jobs\ImportPrintLog;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    Bus::fake();
    Event::fake();
});

// outside the scheduled time
test('schedule out of time, does not trigger jobs', function ($datetime, $job) {
    testTime()->freeze($datetime);

    $this->artisan('schedule:run');

    Event::assertNotDispatched(ScheduledTaskStarting::class);

    Bus::assertNothingDispatched();
})->with([
    ['2020-10-20 00:59:59', ImportCorporateStructure::class],
    ['2020-10-20 01:59:59', ImportPrintLog::class],
]);

// Happy path
test('schedule at the correct time, triggers the scheduled jobs', function ($datetime, $job) {
    testTime()->freeze($datetime);

    $this->artisan('schedule:run');

    Event::assertDispatched(ScheduledTaskFinished::class, function ($event) use ($job) {
        return strpos($event->task->description, $job) !== false;
    });

    Bus::assertDispatched($job);
})->with([
    ['2020-10-20 01:00:00', ImportCorporateStructure::class],
    ['2020-10-20 02:00:00', ImportPrintLog::class],
]);
