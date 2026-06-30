<?php

namespace Sindscream\SmartScheduler\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Sindscream\SmartScheduler\SchedulerServiceProvider;
use Sindscream\SmartScheduler\Tests\Fixtures\FixtureNoDefaultCommand;
use Sindscream\SmartScheduler\Tests\Fixtures\FixtureScheduleCommand;
use Sindscream\SmartScheduler\Tests\SchedulesInEnvironment;

class EnvironmentAwareCommandTest extends TestCase
{
    use SchedulesInEnvironment;

    protected function getPackageProviders($app): array
    {
        return [SchedulerServiceProvider::class];
    }

    public function test_it_applies_the_environment_specific_frequency(): void
    {
        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'production',
            '*/15 * * * *'
        );

        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'staging',
            '0 * * * *'
        );
    }

    public function test_it_falls_back_to_the_default_frequency(): void
    {
        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'local',
            '0 0 * * *'
        );
    }

    public function test_it_does_not_schedule_when_environment_is_absent_and_no_default(): void
    {
        $this->assertNotScheduled(FixtureNoDefaultCommand::class, 'local');
    }

    public function test_it_skips_scheduling_in_testing_by_default(): void
    {
        $this->assertNotScheduled(FixtureScheduleCommand::class, 'testing');
    }

    public function test_it_schedules_in_testing_when_explicitly_enabled(): void
    {
        config(['env-scheduler.run_in_tests' => true]);

        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'testing',
            '0 0 * * *'
        );
    }
}
