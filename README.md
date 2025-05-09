# Trackerjack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)

Trackerjack is a powerful Laravel package for tracking website visits and events. It provides a simple way to monitor user behavior, track UTM parameters, and analyze visitor patterns in your Laravel application.

## Features

- Automatic visit tracking with middleware
- UTM parameter tracking
- Custom event tracking
- User attribution
- Terminal UI for viewing tracking data
- Automatic data cleanup
- Configurable tracking rules
- Privacy-focused with configurable cookie settings

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
php artisan vendor:publish --tag="trackerjack-config"
```

## Configuration

The configuration file (`config/trackerjack.php`) allows you to customize various aspects of the tracking:

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
];
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

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mike Wall](https://github.com/daikazu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
