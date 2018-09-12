<?php
namespace MQK\Queue\Outbound\Impl;


use MQK\Queue\Outbound\RouterEntry;
use MQK\Queue\Outbound\NotificationCenter;
use MQK\Queue\Outbound\RouterEntryRepository;
use MQK\Queue\Outbound\Message;
use MQK\Queue\Outbound\OutboundService;

class OutboundServiceImpl implements OutboundService
{
    /**
     * @var RouterEntryRepository
     */
    private $routerEntryRepository;

    /**
     * @var NotificationCenter
     */
    private $notificationCenter;

    public function __construct(
        $routerEntryRepository,
        $notificationCenter) {

        $this->routerEntryRepository = $routerEntryRepository;
        $this->notificationCenter = $notificationCenter;
    }

    public function launch($routerKey, $message)
    {
        $routerEntryList = $this->routerEntryRepository->findByRouterKey($routerKey);

        foreach ($routerEntryList as $routerEntry) {
            $this->notificationCenter->notify($routerEntry, $message);
        }
    }

    public function addNewEndpoint(RouterEntry $entry)
    {
        // TODO: Implement addNewEndpoint() method.
    }

    public function listEndpoint()
    {
        // TODO: Implement listEndpoint() method.
    }
}