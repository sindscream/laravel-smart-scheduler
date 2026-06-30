<?php

namespace Sindscream\SmartScheduler\Tests;

use Illuminate\Console\Scheduling\Schedule;
use Sindscream\SmartScheduler\Console\Scheduling\EnvironmentAwareCommand;

trait SchedulesInEnvironment
{
    /**
     * Register the command on a fresh schedule for the given environment and
     * return the resulting schedule events.
     *
     * @return \Illuminate\Console\Scheduling\Event[]
     */
    protected function scheduleFor(string $commandClass, string $environment): array
    {
        $this->app['env'] = $environment;

        $schedule = new Schedule();

        /** @var EnvironmentAwareCommand $command */
        $command = $this->app->make($commandClass);
        $command->schedule($schedule);

        return $schedule->events();
    }

    protected function assertScheduledWithExpression(
        string $commandClass,
        string $environment,
        string $expression
    ): void {
        $events = $this->scheduleFor($commandClass, $environment);

        $this->assertCount(
            1,
            $events,
            "Command [{$commandClass}] should be scheduled exactly once in [{$environment}]."
        );

        $this->assertSame(
            $expression,
            $events[0]->getExpression(),
            "Command [{$commandClass}] has an unexpected cron expression in [{$environment}]."
        );
    }

    protected function assertNotScheduled(string $commandClass, string $environment): void
    {
        $events = $this->scheduleFor($commandClass, $environment);

        $this->assertCount(
            0,
            $events,
            "Command [{$commandClass}] should not be scheduled in [{$environment}]."
        );
    }
}
