<?php
namespace MQK\Queue\Outbound\Impl;


use GuzzleHttp\Client;
use MQK\Queue\MessageNormal;
use MQK\Queue\Outbound\NotificationCenter;
use MQK\Queue\Outbound\RouterEntry;

class NotificationCenterImpl implements NotificationCenter
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function notify(RouterEntry $routerEntry, MessageNormal $message)
    {
        $this->client->post($routerEntry->endpoint(), [
            'json' => $message->jsonSerialize()
        ]);
    }
}