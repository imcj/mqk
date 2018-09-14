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
            try {
                $this->notificationCenter->notify($routerEntry, $message);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry(RouterEntry $routerEntry)
    {
        $this->routerEntryRepository->addNewRouterEntry($routerEntry);
    }

    public function listEndpoint()
    {
        // TODO: Implement listEndpoint() method.
    }

    /**
     * @param string $routerKey
     * @param int $page
     * @return RouterEntry
     */
    public function listRouterEntry($routerKey, $page)
    {

    }
}