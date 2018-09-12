<?php
namespace MQK\Queue\Outbound\Impl;


use MQK\Queue\Outbound\Command\CreateRouterEntryCommand;
use MQK\Queue\Outbound\DTO\RouterEntryDTO;
use MQK\Queue\Outbound\OutboundService;
use MQK\Queue\Outbound\OutboundServiceFacade;
use MQK\Queue\Outbound\RouterEntry;
use MQK\Queue\Outbound\RouterEntryRepository;

class OutboundServiceFacadeImpl implements OutboundServiceFacade
{

    /**
     * @var RouterEntryRepository
     */
    private $routerEntryRepository;

    /**
     * @var OutboundService
     */
    private $outboundService;

    public function __construct($outboundService)
    {
        $this->outboundService = $outboundService;
    }

    /**
     * @param string $routerKey
     * @param int $page
     * @return RouterEntryDTO[]
     */
    public function listRouterEntry($routerKey, $page)
    {
        // TODO: Implement listRouterEntry() method.
    }

    /**
     * @param CreateRouterEntryCommand $command
     * @return RouterEntry
     */
    public function addNewRouterEntry(CreateRouterEntryCommand $command)
    {
        // TODO: Implement addNewRouterEntry() method.
    }

    /**
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function updateRouterEntry(RouterEntry $routerEntry)
    {
        // TODO: Implement updateRouterEntry() method.
    }

    /**
     * @param int $id
     * @return RouterEntry
     */
    public function removeRouterEntry($id)
    {
        // TODO: Implement removeRouterEntry() method.
    }

    public function launch($routerKey, $message)
    {
        $this->outboundService->launch($routerKey, $message);
    }
}