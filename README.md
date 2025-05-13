# Trackerjack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/trackerjack/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/trackerjack/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/trackerjack.svg?style=flat-square)](https://packagist.org/packages/daikazu/trackerjack)

A Laravel package for tracking visitor behavior and events with advanced analytics capabilities.

## Features

- Automatic visit tracking with middleware
- Custom event tracking
- UTM parameter tracking (including gclid)
- User binding for anonymous events
- High-performance queue-based processing
- Batch processing for efficient database writes
- Terminal UI for data analysis and visualization
- Automatic data cleanup
- Visitor fingerprinting
- Event whitelisting
- Route exclusions
- Configurable cookie settings
- Type-safe data handling with DTOs
- Advanced visitor journey analysis
- Event sequence tracking
- Time-based analytics
- UTM attribution analysis

## Requirements

- PHP 8.4+
- Laravel 10.0+ or 11.0+ or 12.0+

## Installation

```bash
composer require daikazu/trackerjack
```

## Usage

### Basic Setup

1. Publish the configuration file:
```bash
php artisan vendor:publish --provider="Daikazu\Trackerjack\TrackerjackServiceProvider"
```

2. Run the migrations:
```bash
php artisan migrate
```

3. Add the middleware to your `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Daikazu\Trackerjack\Http\Middleware\TrackVisits::class,
    ],
];
```

### Tracking Events

```php
use Daikazu\Trackerjack\DataTransferObjects\EventData;
use Daikazu\Trackerjack\Jobs\ProcessEvent;

// Create an event
$eventData = new EventData(
    visitorId: 'abc123',
    eventName: 'purchase',
    payload: ['amount' => 99.99],
    userId: 1,
    email: 'user@example.com'
);

// Dispatch the event
ProcessEvent::dispatch($eventData);
```

### Frontend Event Tracking

Trackerjack provides a simple JavaScript helper for tracking events from your frontend code. First, include the JavaScript file in your layout:

Add the following to your head:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```html
<script src="{{ asset('js/trackerjack.js') }}"></script>
```

Then you can track events from anywhere in your JavaScript code:

```javascript
// Track a simple event
Trackerjack.track('button_click');

// Track an event with payload
Trackerjack.track('purchase', {
    amount: 99.99,
    currency: 'USD',
    product_id: 123
});

// Track an event with async/await
async function handlePurchase() {
    try {
        await Trackerjack.track('purchase_completed', {
            order_id: '12345',
            total: 149.99
        });
        console.log('Purchase tracked successfully');
    } catch (error) {
        console.error('Failed to track purchase:', error);
    }
}
```

The frontend tracking implementation includes:
- Automatic CSRF token handling
- Proper error handling
- Promise-based API for modern async/await usage
- Automatic visitor ID inclusion
- Automatic user ID and email inclusion for authenticated users

### Data Transfer Objects

Trackerjack uses Data Transfer Objects (DTOs) to ensure type safety and data consistency. The package provides two main DTOs:

#### VisitData

```php
use Daikazu\Trackerjack\DataTransferObjects\VisitData;

$visitData = new VisitData(
    visitorId: 'abc123',
    url: 'https://example.com',
    referrer: 'https://google.com',
    utmSource: 'google',
    utmMedium: 'cpc',
    utmCampaign: 'spring_sale',
    utmTerm: 'shoes',
    utmContent: 'banner1',
    gclid: 'abc123xyz',
    ipAddress: '127.0.0.1',
    userAgent: 'Mozilla/5.0...'
);
```

#### EventData

```php
use Daikazu\Trackerjack\DataTransferObjects\EventData;

$eventData = new EventData(
    visitorId: 'abc123',
    eventName: 'purchase',
    payload: ['amount' => 99.99],
    userId: 1,
    email: 'user@example.com'
);
```

Both DTOs provide:
- Type safety with strict typing
- Immutability through readonly properties
- Array conversion methods (`fromArray` and `toArray`)
- Automatic timestamp handling

### Terminal UI

Trackerjack includes a terminal UI for analyzing your tracking data. Run the following command to access it:

```bash
php artisan trackerjack:tui
```

The terminal UI provides:
- Recent activity overview
- Visitor analysis
- Event statistics
- UTM attribution analysis
- Visitor journey tracking
- Time-based analytics
- Event sequence analysis

### Performance Features

1. **Queue-Based Processing**: All tracking operations are processed asynchronously through Laravel's queue system.

2. **Batch Processing**: Visits are collected and processed in batches to reduce database load.

3. **Configurable Batch Size**: Adjust the batch size based on your server's capabilities:
   ```env
   TRACKERJACK_BATCH_SIZE=100
   ```

4. **Multiple Queue Workers**: Scale horizontally by running multiple queue workers.

5. **Memory Efficient**: Uses bulk inserts for better performance.

6. **Type-Safe Data Handling**: DTOs ensure data consistency and type safety throughout the application.

### Data Cleanup

Trackerjack includes commands to clean up old tracking data:

```bash
# Clean up old visits
php artisan trackerjack:prune-visits

# Clean up old events
php artisan trackerjack:prune-events
```

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
        'gclid',
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
