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

    public function __construct(
        RouterEntryRepository $routerEntryRepository,
        OutboundService $outboundService) {

        $this->routerEntryRepository = $routerEntryRepository;
        $this->outboundService = $outboundService;
    }

    /**
     * @param string $routerKey
     * @param int $page
     * @return RouterEntryDTO[]
     */
    public function listRouterEntry($routerKey, $page)
    {
        $routerEntryList = $this
            ->routerEntryRepository
            ->findByRouterKey($routerKey);

        return array_map(function(RouterEntry $routerEntry) {
            return new RouterEntryDTO(
                $routerEntry->id(),
                $routerEntry->routerKey(),
                $routerEntry->endpoint()
            );
        }, $routerEntryList);
    }

    /**
     * @param CreateRouterEntryCommand $command
     * @return RouterEntry
     */
    public function addNewRouterEntry(CreateRouterEntryCommand $command)
    {
        return $this->outboundService->addNewRouterEntry(
            new RouterEntry(
                null,
                $command->routerKey(),
                $command->endpoint(),
                $command->description(),
                null,
                null
            )
        );
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