# Laravel API Health

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)
[![Build Status](https://img.shields.io/travis/pbmedia/laravel-api-health/master.svg?style=flat-square)](https://travis-ci.org/pbmedia/laravel-api-health)
[![Quality Score](https://img.shields.io/scrutinizer/g/pbmedia/laravel-api-health.svg?style=flat-square)](https://scrutinizer-ci.com/g/pbmedia/laravel-api-health)
[![Total Downloads](https://img.shields.io/packagist/dt/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)

This is a package to monitor first and third party services that your app uses. It can send a notification if a service goes down (or up!) and supports scheduling. You can create *checkers* for whatever API or service you want to monitor but it also comes with some built-in checkers so you can fire up some checkers really quickly.

## Requirements

* Laravel 5.5+ only, 7.1 and 7.2 supported.
* Support for [Package Discovery](https://laravel.com/docs/5.6/packages#package-discovery).

## Installation

You can install the package via composer:

```bash
composer require pbmedia/laravel-api-health
```

Publish the translation resources and config file using the Artisan CLI tool.

```bash
php artisan vendor:publish --provider="Pbmedia\ApiHealth\ApiHealthServiceProvider"
```

## Build your first checker

So let's create our first checker. Assume you want to request an URL and verify that the status code of the response is in the 200 range. You can build this checker on your own using the `make:checker` command, but this one we've pre-built for you. Open up the terminal and let's make a HTTP checker!

```bash
php artisan make:http-get-checker LaravelDocumentationChecker
```

In your `app` folder you'll find a new `Checkers` folder with the newly created checker. The only thing you have to do is adjust the `$url` property to your needs:

```php
<?php

namespace App\Checkers;

use GuzzleHttp\Client;
use Pbmedia\ApiHealth\Checkers\AbstractHttpGetChecker;

class LaravelDocumentationChecker extends AbstractHttpGetChecker
{
    /*
     * The URL you want to request.
     */
    protected $url = 'https://laravel.com/docs/5.6';

    /*
     * Here you can specify the Guzzle HTTP options.
     *
     * @return \Pbmedia\ApiHealth\Checkers\AbstractHttpGetChecker
     */
    public static function create()
    {
        return new static(new Client, [
            'timeout' => 5,
        ]);
    }

    /**
     * Defines the checker's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Event  $event
     * @return null
     */
    public function schedule(Event $event)
    {
        $event->everyMinute();

        // $event->evenInMaintenanceMode();
        // $event->onOneServer();
    }
}
```

Now we can run this checker in the console with the following command:
```bash
php artisan api-health:check App\Checkers\LaravelDocumentationChecker
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@pbmedia.be instead of using the issue tracker.

## Credits

- [Pascal Baljet](https://github.com/pascalbaljet)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
