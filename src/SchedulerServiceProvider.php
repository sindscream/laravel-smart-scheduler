<?php

namespace Sindscream\SmartScheduler;

use Sindscream\SmartScheduler\Console\Scheduling\EnvironmentAwareCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

class SchedulerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/env-scheduler.php', 'env-scheduler');
    }

    public function boot(): void
    {
        // Scheduling and command registration are only relevant for the
        // console (e.g. `schedule:run`), so skip the discovery overhead on
        // web requests entirely.
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/env-scheduler.php' => config_path('env-scheduler.php'),
        ], 'env-scheduler-config');

        $commands = $this->discoverCommands();

        $this->commands($commands);

        $this->app->booted(function () use ($commands) {
            $this->scheduleCommands($commands);
        });
    }

    protected function scheduleCommands(array $commands): void
    {
        $schedule = $this->app->make(Schedule::class);

        foreach ($commands as $commandClass) {
            $command = $this->app->make($commandClass);

            if ($command instanceof EnvironmentAwareCommand) {
                $command->schedule($schedule);
            }
        }
    }

    protected function discoverCommands(): array
    {
        $commands = [];
        $commandPath = app_path('Console/Commands');

        if (!is_dir($commandPath)) {
            return $commands;
        }

        $finder = new Finder();
        $finder->files()->in($commandPath)->name('*Command.php');

        foreach ($finder as $file) {
            $class = $this->getClassFromFile($file->getRealPath());

            if ($class && is_subclass_of($class, EnvironmentAwareCommand::class)) {
                $commands[] = $class;
            }
        }

        return $commands;
    }

    protected function getClassFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);

        // Extract namespace
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class name
        $class = null;
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        return $namespace ? "{$namespace}\\{$class}" : null;
    }
}