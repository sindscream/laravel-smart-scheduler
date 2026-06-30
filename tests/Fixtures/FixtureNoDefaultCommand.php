<?php

namespace Sindscream\SmartScheduler\Tests\Fixtures;

use Illuminate\Console\Scheduling\Event;
use Sindscream\SmartScheduler\Console\Scheduling\EnvironmentAwareCommand;

class FixtureNoDefaultCommand extends EnvironmentAwareCommand
{
    protected $signature = 'fixture:no-default';
    protected $description = 'Fixture command without a default schedule fallback';

    public function scheduleConfig(): array
    {
        return [
            'production' => fn (Event $event) => $event->everyFifteenMinutes(),
        ];
    }

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
