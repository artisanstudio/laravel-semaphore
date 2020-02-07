<?php

namespace Humans\Semaphore;

use Zttp\Zttp;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Notification;

class SemaphoreChannel
{
    /**
     * Semaphore's API endpoint to send messages.
     */
    const MESSAGE_API = 'http://api.semaphore.co/api/v4/messages';

    /**
     * Send the SMS notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSemaphore($notifiable);

        $response = Zttp::post(static::MESSAGE_API, [
            'number'     => $number = $notifiable->routeNotificationForSemaphore(),
            'message'    => $message->getContent(),
            'sendername' => $sender = $message->getFrom(),
            'apikey'     => $apiKey = Config::get('semaphore.key'),
        ])->json();

        if (array_key_exists('apikey', $response)) {
            // "apikey" => array:1 [
            //     0 => "The selected apikey is invalid."
            // ]
            throw new Exceptions\InvalidApiKey($apiKey);
        }

        if (array_key_exists(0, $response) && array_key_exists('senderName', $response[0])) {
            // 0 => array:1 [
            //     "senderName" => "The senderName supplied is not valid"
            // ]
            throw new Exceptions\InvalidSenderName($sender);
        }

        if (array_key_exists('number', $response)) {
            // "number" => array:1 [
            //     0 => "The number format is invalid."
            // ]
            throw new Exceptions\InvalidNumber($number);
        }
    }
}
