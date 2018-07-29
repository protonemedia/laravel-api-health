# Laravel API Health

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)
[![Build Status](https://img.shields.io/travis/pascalbaljetmedia/laravel-api-health/master.svg?style=flat-square)](https://travis-ci.org/pascalbaljetmedia/laravel-api-health)
[![Quality Score](https://img.shields.io/scrutinizer/g/pascalbaljetmedia/laravel-api-health.svg?style=flat-square)](https://scrutinizer-ci.com/g/pascalbaljetmedia/laravel-api-health)
[![Total Downloads](https://img.shields.io/packagist/dt/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)

This is a package to monitor first and third party services that your app uses. It can send a notification if a service goes down (or up!) and supports scheduling. You can create *checkers* for whatever API or service you want to monitor but it also comes with some built-in checkers so you can fire up some checkers really quickly.

## Requirements

* Laravel 5.6 only, 7.1 and 7.2 supported.
* Support for [Package Discovery](https://laravel.com/docs/5.6/packages#package-discovery).

## Features

* Built-in HTTP and Ssl Certificate checkers
* You can build your own checkers
* It can schedule checkers
* Sends notifications about failed checkers
* Sends notifications when a failed checker recovers
* Caches the status of checkers
* You can fetch the status of checkers in your code
* It can print the status of the checkers in the console
* Customize notifications per checker (optionally)

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
use Illuminate\Console\Scheduling\Event;
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

## Schedule your checkers

You can fill the `checkers` array in the `config/api-health.php` file with all the checkers you want to schedule. By default every checker will run every minute. The `schedule` method on the checker allows you to set a frequency similair to the [Laravel Task Scheduler](https://laravel.com/docs/5.6/scheduling#schedule-frequency-options).

```php
<?php

return [
    'checkers' => [
        \App\Checkers\LaravelDocumentationChecker::class,
    ],

    //
];
```

Open the `App\Console\Kernel` class in your editor and add the `api-health:run-checkers` command and set it to `everyMinute()`. If you don't use Laravel's Task Scheduler you could also manually create a cronjob that runs every minute. The `api-health:run-checkers` command will figure out what checker should run or not based on the configured schedules, if you want to ignore the scheduling and run all checkers, just run the command with the `--force` option.

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    //

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('api-health:run-checkers')->everyMinute();
    }

    //
}
```

The result of the checker will be cached but it refreshes every time you run a checker in the console. This way you can fetch the cached result in your PHP code. This is great for checking wether a service is online without having to wait for the result.

For example, you might use a payment gateway in your app. If you check the status of the gateway every minute through the scheduler, you can respond to that status pretty accurately in your UI. You can use the `ApiHealth` facade to obtain the status of a checker. If you don't want to use the cache you can use the `fresh` method to ignore the stored state.

```php
use App\Checkers\LaravelDocumentationChecker;
use Pbmedia\ApiHealth\Facades\ApiHealth;

ApiHealth::isFailing(LaravelDocumentationChecker::class);
ApiHealth::isPassing(LaravelDocumentationChecker::class);

ApiHealth::fresh()->isFailing(LaravelDocumentationChecker::class);
ApiHealth::fresh()->isPassing(LaravelDocumentationChecker::class);
```

## Create your own checker

Building a checker is quite easy. Run the `make:checker` command and pass the name of your checker as an argument:

```bash
php artisan make:checker GetIpAddressByHost
```

There are two methods you need to fill. The `create` method is used as a factory to build and configure an instance of your checker. In this case it's quite simple but this the place to gather and configure your dependencies. The `run` methods performs the actual check and must throw a `\Pbmedia\ApiHealth\Checkers\CheckerHasFailed` exception if something goes wrong. Here is an example:

```php
<?php

namespace App\Checkers;

use Illuminate\Console\Scheduling\Event;
use Pbmedia\ApiHealth\Checkers\AbstractChecker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class GetIpAddressByHost extends AbstractChecker
{
    public static function create()
    {
        return new static;
    }

    public function schedule(Event $event)
    {
        $event->everyMinute();
    }

    public function run()
    {
        $ip = gethostbyname('www.pascalbaljetmedia.com');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new CheckerHasFailed("Host www.pascalbaljetmedia.com did not return a valid IP Address.");
        }
    }
}
```

## Other built-in checkers

* `make:ssl-certificate-checker` - Ssl Certificate validation (which uses [spatie/ssl-certificate](https://github.com/spatie/ssl-certificate)!)

## Notification options

The config file has a notifcation section which allows you to configure the channels and change the Notifiable class. There are two default notifications, `CheckerHasFailed` and `CheckerHasRecovered`, you can swap them in the config file for your own notifications. There is also an option to resend the `CheckerHasFailed` notification after a number of minutes:

```php
<?php

return [
    //

    'notifications' => [
        /**
         *  Number of minutes until send the failed notification again.
         */
        'resend_failed_notification_after_minutes' => 60,

        /**
         * Class name of the failed notification.
         */
        'default_failed_notification' => \Pbmedia\ApiHealth\Notifications\CheckerHasFailed::class,

        /**
         * Class name of the recovered notification.
         */
        'default_recovered_notification' => \Pbmedia\ApiHealth\Notifications\CheckerHasRecovered::class,
    ],

    //
]
```

You can also set these notifications options *per checker*. Just modify these properties on your checker and the package will do the rest:

```php
<?php

class MyChecker extends AbstractChecker
{
    protected $resendFailedNotificationAfterMinutes = 30;

    protected $failedNotificationClass = \App\Notifications\Whoops::class;

    protected $recoveredNotificationClass \App\Notifications\Yay::class;
}
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
