# Cron Job Manager

Web-based interface for managing cron jobs with monitoring, history, and failure alerts.

## Features

- Create/edit/delete cron jobs via web UI
- Visual cron expression builder
- Execution history and logs
- Email alerts on failure
- Job dependencies
- Timezone support
- Run as specific user
- Disable/enable jobs
- Manual trigger

## Usage

```php
// Define job
CronJob::create([
    'name' => 'Backup Database',
    'command' => 'php artisan db:backup',
    'expression' => '0 2 * * *', // 2 AM daily
    'enabled' => true
]);

// With monitoring
CronJob::create([...])
    ->onFailure(function($output) {
        Mail::to('admin@example.com')->send(...);
    })
    ->timeout(3600); // 1 hour max
```

## Web Interface

- Dashboard with upcoming/running jobs
- Execution timeline
- Success/failure statistics
- Quick enable/disable toggle
- Test run button
- Log viewer

## CLI

```bash
# List jobs
php artisan cron:list

# Run specific job
php artisan cron:run {id}

# Show next run times
php artisan cron:schedule
```

## Cron Expression Examples

- `* * * * *` - Every minute
- `0 * * * *` - Every hour
- `0 2 * * *` - Daily at 2 AM
- `0 0 * * 0` - Weekly on Sunday
- `0 0 1 * *` - Monthly on 1st

## Requirements

- PHP 7.3+
- Laravel 6.0
