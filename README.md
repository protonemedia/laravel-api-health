# Laravel API Health

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)
[![Build Status](https://img.shields.io/travis/pascalbaljetmedia/laravel-api-health/master.svg?style=flat-square)](https://travis-ci.org/pascalbaljetmedia/laravel-api-health)
[![Quality Score](https://img.shields.io/scrutinizer/g/pascalbaljetmedia/laravel-api-health.svg?style=flat-square)](https://scrutinizer-ci.com/g/pascalbaljetmedia/laravel-api-health)
[![Total Downloads](https://img.shields.io/packagist/dt/pbmedia/laravel-api-health.svg?style=flat-square)](https://packagist.org/packages/pbmedia/laravel-api-health)

This is a package to monitor first and third party services that your app uses. It can send a notification if a service goes down (or up!) and supports scheduling. You can create *checkers* for whatever API or service you want to monitor but it also comes with some built-in checkers so you can fire up some checkers really quickly.

## Requirements

* Laravel 5.7 only, PHP 7.1 and 7.2 supported.
* Support for [Package Discovery](https://laravel.com/docs/5.7/packages#package-discovery).

## Features

* Built-in HTTP and Ssl Certificate checkers
* You can build your own checkers
* It can schedule checkers
* Automatic retries of failed checkers
* Sends notifications about failed checkers
* Sends notifications when a failed checker recovers
* Caches the status of checkers
* You can fetch the status of checkers in your code
* It can print the status of the checkers in the console
* Customize notifications per checker (optionally)
* Ability to fake the ApiHealth facade to test your app

## Installation

You can install the package via composer:

```bash
composer require pbmedia/laravel-api-health
```

If you're still using Laravel 5.6, please use version 1.2.3 (which is not maintained anymore).

Publish the translation resources and config file using the Artisan CLI tool.

```bash
php artisan vendor:publish --provider="Pbmedia\ApiHealth\ApiHealthServiceProvider"
```

## Build your first checker

So let's create our first checker. Assume you want to request an URL and verify that the status code of the response is in the 200 range. You can build this checker on your own using the `make:checker` command, but this one we've pre-built for you. Open up the terminal and let's make a HTTP checker!

```bash
php artisan make:http-checker LaravelDocumentationChecker
```

In your `app` folder you'll find a new `Checkers` folder with the newly created checker. The only thing you have to do is adjust the `$url` property to your needs:

```php
<?php

namespace App\Checkers;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Event;
use Pbmedia\ApiHealth\Checkers\AbstractHttpChecker;

class LaravelDocumentationChecker extends AbstractHttpChecker
{
    /*
     * The URL you want to request.
     */
    protected $url = 'https://laravel.com/docs/5.7';

    /*
     * The method you want to use.
     */
    protected $method = 'GET';

    /*
     * Here you can specify the Guzzle HTTP options.
     *
     * @return \Pbmedia\ApiHealth\Checkers\AbstractHttpChecker
     */
    public static function create()
    {
        return new static(new Client, [
            // 'headers' => [
            //     'X-Foo' => ['Bar', 'Baz'],
            // ],
            // 'json'    => ['foo' => 'bar'],
            // 'timeout' => 5,
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
php artisan api-health:check App\\Checkers\\LaravelDocumentationChecker
```

## Schedule your checkers

You can fill the `checkers` array in the `config/api-health.php` file with all the checkers you want to schedule. By default every checker will run every minute. The `schedule` method on the checker allows you to set a frequency similair to the [Laravel Task Scheduler](https://laravel.com/docs/5.7/scheduling#schedule-frequency-options).

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

## Events

This package dispatches there different events:

* `Pbmedia\ApiHealth\Events\CheckerHasFailed`
* `Pbmedia\ApiHealth\Events\CheckerHasRecovered`
* `Pbmedia\ApiHealth\Events\CheckerIsStillFailing`

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

## Automatic retries

It is possible to specify a number of retries to perform before your checker gets in a failed state. When a retry occurs, a job is sent to the [queue](https://laravel.com/docs/5.7/queues) which will run the checker again. In the config file you can set the number of retries, the job to dispatch (we've created one for you!) and the configuration of the *retry job* such as the connection, delay and queue.

For example, if you set `allowed_retries` to `3` and `delay` to `20`, the checker will run four times in total and will fail after a minute (measured from the first time you ran the checker).

```php
<?php

// config/api-health.php

return [
    //

    'retries' => [
        /**
         * The number of allowed retries.
         */
        'allowed_retries' => 0,

        /**
         * Here you can specify the configuration of the retry job.
         */
        'job' => [
            'job' => \Pbmedia\ApiHealth\Jobs\RetryChecker::class,

            'connection' => null,

            'delay' => null,

            'queue' => null,
        ],
    ],

    //
]
```

Just as the notification options, you can set the number of *allowed retries* and the class of the job *per checker*. If you would like to interact with the job before it is sent to the queue, you could use the `withRetryJob` method. This method receives the job, allowing you to call any of its methods before the job is actually dispatched:

```php
<?php

class MyChecker extends AbstractChecker
{
    protected $allowedRetries = 2;

    protected $retryJob = \App\Jobs\RetryChecker::class;

    public function withRetryJob($job)
    {
        $job->delay(now()->addMinutes(3));
    }
}
```

## Advanced

Every checker should be able to identify itself so the state can be stored. The `AbstractChecker` has an `id` method which simply returns the name of the class, in most cases you don't have to worry about the identifier but there is a scenario in which you need to override this method. Say you want to reuse a checker with different arguments. In this example there is a `Server` model which has an `isOnline` method.

```php
class Server extends Model
{
    public function isOnline(): bool
    {
        //
    }
}
```

We've generated this checker with the `make:checker ServerChecker` command and added a custom `id` method.

```php
<?php

namespace App\Checkers;

use App\Models\Server;
use Pbmedia\ApiHealth\Checkers\AbstractChecker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class ServerChecker extends AbstractChecker
{
    public $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public static function create(Server $server)
    {
        return new static($server);
    }

    public function id(): string
    {
        return 'server_' . $this->server->id;
    }

    public function run()
    {
        if (!$this->server->isOnline()) {
            throw new CheckerHasFailed("Server {$this->server->name} is offline.");
        }
    }
}
```

Now if you want to verify the status of multiple server, you could easily do something like this:

```php
<?php

use App\Models\Server;
use Pbmedia\ApiHealth\Runner;

$serverA = Server::whereIpAddress('1.1.1.1')->first();
$serverB = Server::whereIpAddress('8.8.8.8')->first();

$runner = new Runner([$serverA, $serverB]);

// or

$runner = new Runner(Server::all());

$onlineServers = $runner->passes();
$offlineServers = $runner->failed();

```

## Writing tests

The `ApiHealth` facade has a `fake` method which swaps the bound instance with a fake one. This allows you to force the state of a checker. Mind that this only works on the facade, the checker itself will be untouched.

```php
<?php

namespace App\Tests;

use App\Checkers\FailingChecker;
use App\Checkers\PassingChecker;
use Pbmedia\ApiHealth\Facades\ApiHealth;

class MyTest extends TestCase
{
    /** @test */
    public function it_can_make_the_passing_checker_fail()
    {
        ApiHealth::fake();

        ApiHealth::mustFail(PassingChecker::class);
        ApiHealth::mustPass(FailingChecker::class);

        $this->assertTrue(ApiHealth::isFailing(PassingChecker::class));
        $this->assertTrue(ApiHealth::isPassing(FailingChecker::class));
    }
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

If you discover any security related issues, please email info@pascalbaljetmedia.com instead of using the issue tracker.

## Credits

- [Pascal Baljet](https://github.com/pascalbaljet)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
