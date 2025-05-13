# TrackerJack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)

A high-performance visitor tracking package for Laravel applications.

## Features

- Automatic visit tracking with middleware
- Custom event tracking
- UTM parameter tracking
- User binding for anonymous events
- High-performance queue-based processing
- Batch processing for efficient database writes
- Terminal UI for data analysis
- Automatic data cleanup

## Installation

You can install the package via composer:

```bash
composer require daikazu/trackerjack
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="trackerjack-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Daikazu\Trackerjack\TrackerjackServiceProvider"
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Daikazu\Trackerjack\TrackerjackServiceProvider"
```

### Queue Configuration

For optimal performance, configure your queue driver in your Laravel application:

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),
```

Set up your environment variables:

```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Optional TrackerJack queue settings
TRACKERJACK_BATCH_SIZE=100
TRACKERJACK_QUEUE=default
TRACKERJACK_RETRY_AFTER=60
TRACKERJACK_TRIES=3
```

### Queue Worker Setup

For production environments, set up queue workers using Supervisor:

```ini
[program:trackerjack-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --queue=default --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/project/worker.log
stopwaitsecs=3600
```

## Usage

### Automatic Visit Tracking

Add the middleware to your routes or middleware groups:

```php
// In your `app/Http/Kernel.php`
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Daikazu\Trackerjack\Http\Middleware\TrackVisits::class,
    ],
];
```

### Tracking Custom Events

```php
use Daikazu\Trackerjack\Facades\Trackerjack;

// Track a simple event
Trackerjack::trackEvent('button_clicked');

// Track an event with additional data
Trackerjack::trackEvent('form_submitted', [
    'form_id' => 'contact',
    'fields' => ['name', 'email'],
]);
```

### Binding Events to Users

When a user logs in, you can bind their previous anonymous events to their account:

```php
use Daikazu\Trackerjack\Facades\Trackerjack;

// In your login handler
Trackerjack::bindToUser($user);
```

### Viewing Tracking Data

Use the terminal UI to view tracking data:

```bash
php artisan trackerjack:tui
```

### Data Cleanup

The package includes commands to clean up old tracking data:

```bash
# Clean up old visits
php artisan trackerjack:prune-visits

# Clean up old events
php artisan trackerjack:prune-events
```

## Performance Optimization

TrackerJack is optimized for high-traffic applications:

1. **Queue-Based Processing**: All tracking operations are processed asynchronously through Laravel's queue system.

2. **Batch Processing**: Visits are collected and processed in batches to reduce database load.

3. **Configurable Batch Size**: Adjust the batch size based on your server's capabilities:
   ```env
   TRACKERJACK_BATCH_SIZE=100
   ```

4. **Multiple Queue Workers**: Scale horizontally by running multiple queue workers.

5. **Memory Efficient**: Uses bulk inserts for better performance.

## Configuration Options

```php
return [
    'cookie' => [
        'name' => 'trackerjack_id',
        'ttl' => 60 * 24 * 365, // 1 year in minutes
        'domain' => env('SESSION_DOMAIN'),
    ],

    'utm_parameters' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ],

    'excluded_routes' => [
        'admin/*',
        'api/*',
        'horizon/*',
        'telescope/*',
    ],

    'cleanup' => [
        'visits_older_than' => 60 * 24 * 30, // 30 days in minutes
        'events_older_than' => 60 * 24 * 90, // 90 days in minutes
    ],

    'allowed_events' => null, // Set to null to allow all events

    'uniqueness' => true, // Enable/disable unique visitor tracking

    'queue' => [
        'batch_size' => env('TRACKERJACK_BATCH_SIZE', 100),
        'queue_name' => env('TRACKERJACK_QUEUE', 'default'),
        'retry_after' => env('TRACKERJACK_RETRY_AFTER', 60),
        'tries' => env('TRACKERJACK_TRIES', 3),
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@daikazu.com instead of using the issue tracker.

## Credits

- [Mike Wall](https://github.com/mikewall)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
