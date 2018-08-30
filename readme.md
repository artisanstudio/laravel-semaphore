# Laravel Semaphore

[Semaphore](semaphore.co) is a Philippine SMS Service provider.

## Installation

```
composer require artisan/laravel-semaphore
```

If you're using Laravel 5.5's package discovery, then there's no need to add the service provider.

Otherwise, add the Semaphore service provider in your `config/app.php`

```php
return [
    'providers' => [
        // ...

        Artisan\Semaphore\ServiceProvider::class,
    ],
]
```

## Configuration

In your `.env` file, copy this default template and you can then add your Semaphore API key and sender name.

```
SEMAPHORE_KEY=
SEMAPHORE_FROM_NAME=
```

If you want to customize the config file, publish the config file via:

```bash
php artisan vendor:publish --tags=semaphore
```

## Usage

After creating a notification, we can start using the `Aritsan\Semaphore\SemaphoreChannel` to send out your SMS.

You should define a `toSemaphore` method on the notification class. This method will receive a `$notifiable` entity and should return an `Artisan\Semaphore\SemahporeMessage` instance:

```php
use Artisan\Semaphore\SemaphoreChannel;
use Artisan\Semaphore\SemaphoreMessage;

class ReminderMessage extends notification
{
    public function via($notifiable)
    {
        return ['slack', SemaphoreChannel::class];
    }

    public function toSemaphore($notifiable)
    {
        return (new SemaphoreMessage)
                    ->content("Hey {$notifiable->name}, don't forget to brush your teeth!");
    }
}
```


If you would like to send notifications form a sname that is different from the name you specified in your `config/semaphore.php` file, you may use the `from` method on a `SemaphoreMessage` instance:

```php
    public function toSemaphore($notifiable)
    {
        return (new SemaphoreMessage)
                    ->content("Hey {$notifiable->name}, don't forget to brush your teeth!")
                    ->from('Artisan');
    }
```

When sending notifications via the `SemaphoreChannel::class`, the notification system *_won't_* look for any atribute automatically on the notifiable entry. To assign which number the notification is delivered to, define a `routeNotificationForSemaphore` method on the entity:

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    
    /**
     * Route notifications for the Semaphore channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSemaphore($notification)
    {
        return $this->mobile;
    }
}
```
