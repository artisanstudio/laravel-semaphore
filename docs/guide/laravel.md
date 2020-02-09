# Laravel Integration

If you're using Laravel, there's already a built in integration to help out with the set up.

For Laravel 5.5 or greater, the package already adds the service provider via **Package Discovery**.

Otherwise, add the Semaphore service provider in your `config/app.php`

```php
return [
    'providers' => [
        // ...
        Humans\Semaphore\ServiceProvider::class,
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

## Sending Messages with the Facade

With that, you can use the `Semaphore` facade to send messages.

```php
Semaphore::message()->send('0917xxxxxxx', 'Your OTP is X7542.');
```

## Notification Channel

After creating a notification, we can start using the `Humans\Semaphore\Laravel\SemaphoreChannel` to send out your SMS.

You should define a `toSemaphore` method on the notification class. This method will receive a `$notifiable` entity and should return an `Humans\Semaphore\SemahporeMessage` instance:

```php
use Humans\Semaphore\Laravel\SemaphoreChannel;
use Humans\Semaphore\Laravel\SemaphoreMessage;

class ReminderMessage extends notification
{
    public function via($notifiable)
    {
        return ['slack', SemaphoreChannel::class];
    }

    public function toSemaphore($notifiable)
    {
        return (new SemaphoreMessage)
            ->message("Hey {$notifiable->name}, don't forget to brush your teeth!");
    }
}
```

If you would like to send notifications form a sname that is different from the name you specified in your `config/semaphore.php` file, you may use the `from` method on a `SemaphoreMessage` instance:

```php
    public function toSemaphore($notifiable)
    {
        return (new SemaphoreMessage)
                    ->content("Hey {$notifiable->name}, don't forget to brush your teeth!")
                    ->from('Humans');
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
