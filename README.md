# Laravel Smart Scheduler

Environment-aware scheduled commands for Laravel. Declare a different schedule
frequency **per environment** directly on the command, using Laravel's native
scheduling API. No reflection, no custom DSL, no kernel wiring.

```php
public function scheduleConfig(): array
{
    return [
        'production' => fn (Event $event) => $event->everyFifteenMinutes()->weekdays(),
        'staging'    => fn (Event $event) => $event->hourly(),
        'default'    => fn (Event $event) => $event->daily(),
        // an environment that is omitted (and has no 'default') is not scheduled
    ];
}
```

## Requirements

- PHP `^8.3`
- Laravel 10 or 11 (`illuminate/console`, `illuminate/support` `^10.0|^11.0`)

## Installation

```bash
composer require sindscream/laravel-smart-scheduler
```

The service provider is auto-discovered. Optionally publish the config:

```bash
php artisan vendor:publish --tag=env-scheduler-config
```

## Usage

Extend `EnvironmentAwareCommand` and implement `scheduleConfig()`. Each entry
maps an environment name to a closure that receives Laravel's
`Illuminate\Console\Scheduling\Event`, so the full native frequency and
constraint API is available.

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Event;
use Sindscream\SmartScheduler\Console\Scheduling\EnvironmentAwareCommand;

class SyncDataCommand extends EnvironmentAwareCommand
{
    protected $signature = 'sync:data';
    protected $description = 'Synchronize data with an external API';

    public function scheduleConfig(): array
    {
        return [
            'production' => fn (Event $event) => $event
                ->everyFifteenMinutes()
                ->weekdays()
                ->between('9:00', '17:00')
                ->onOneServer(),

            'staging' => fn (Event $event) => $event
                ->hourly()
                ->skip(fn () => app()->isDownForMaintenance()),

            'default' => fn (Event $event) => $event->daily(),
        ];
    }

    public function handle(): int
    {
        // ... your logic ...

        return self::SUCCESS;
    }
}
```

### How it resolves

For the current environment the scheduler picks, in order:

1. The closure under the exact environment key (e.g. `production`).
2. The closure under the `default` key, if present.
3. Otherwise the command is **not** scheduled in that environment.

The closure is applied to the schedule event, so anything Laravel's scheduler
supports works inside it: `everyFifteenMinutes()`, `dailyAt('02:00')`,
`cron('* * * * *')`, `when()`, `skip()`, `withoutOverlapping()`,
`onOneServer()`, `between()`, and so on.

### Registration

Commands placed in your application's `app/Console/Commands` directory that
extend `EnvironmentAwareCommand` are auto-discovered and registered on the
schedule. There is nothing to add to your console kernel.

Make sure your server runs Laravel's scheduler as usual:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing behaviour

Commands are skipped in the `testing` environment by default. To schedule them
under testing (for assertions), enable it via config or env:

```dotenv
ENV_SCHEDULER_RUN_IN_TESTS=true
```

## Configuration

`config/env-scheduler.php`:

| Key            | Default | Description                                                |
| -------------- | ------- | ---------------------------------------------------------- |
| `run_in_tests` | `false` | Whether commands are scheduled in the testing environment. |

## Running the package tests

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
