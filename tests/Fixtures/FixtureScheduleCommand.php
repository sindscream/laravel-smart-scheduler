<?php

namespace Sindscream\SmartScheduler\Tests\Fixtures;

use Illuminate\Console\Scheduling\Event;
use Sindscream\SmartScheduler\Console\Scheduling\EnvironmentAwareCommand;

class FixtureScheduleCommand extends EnvironmentAwareCommand
{
    protected $signature = 'fixture:schedule';
    protected $description = 'Fixture command with a per-environment schedule map';

    public function scheduleConfig(): array
    {
        return [
            'production' => fn (Event $event) => $event->everyFifteenMinutes(),
            'staging' => fn (Event $event) => $event->hourly(),
            'default' => fn (Event $event) => $event->daily(),
        ];
    }

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
