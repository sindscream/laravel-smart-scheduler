<?php

namespace Sindscream\SmartScheduler\Console\Scheduling;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

abstract class EnvironmentAwareCommand extends Command
{
    /**
     * Map the current environment to a Laravel schedule frequency.
     *
     * Each value is a closure that receives the schedule Event and configures
     * it using Laravel's native frequency/constraint methods. Use the
     * "default" key as a fallback; omit an environment to skip scheduling there.
     *
     * @return array<string, Closure(Event): mixed>
     */
    abstract public function scheduleConfig(): array;

    /**
     * Register this command on the schedule for the current environment.
     */
    public function schedule(Schedule $schedule): void
    {
        $environment = app()->environment();

        if ($environment === 'testing' && !config('env-scheduler.run_in_tests', false)) {
            return;
        }

        $config = $this->scheduleConfig();
        $frequency = $config[$environment] ?? $config['default'] ?? null;

        if (!$frequency instanceof Closure) {
            return;
        }

        $frequency($schedule->command($this->getName()));
    }
}
