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

    public function testItAppliesTheEnvironmentSpecificFrequency(): void
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

    public function testItFallsBackToTheDefaultFrequency(): void
    {
        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'local',
            '0 0 * * *'
        );
    }

    public function testItDoesNotScheduleWhenEnvironmentIsAbsentAndNoDefault(): void
    {
        $this->assertNotScheduled(FixtureNoDefaultCommand::class, 'local');
    }

    public function testItSkipsSchedulingInTestingByDefault(): void
    {
        $this->assertNotScheduled(FixtureScheduleCommand::class, 'testing');
    }

    public function testItSchedulesInTestingWhenExplicitlyEnabled(): void
    {
        config(['env-scheduler.run_in_tests' => true]);

        $this->assertScheduledWithExpression(
            FixtureScheduleCommand::class,
            'testing',
            '0 0 * * *'
        );
    }
}
