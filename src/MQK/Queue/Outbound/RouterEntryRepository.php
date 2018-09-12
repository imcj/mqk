<?php
namespace MQK\Queue\Outbound;


interface RouterEntryRepository
{

    /**
     * @param string $routerKey
     * @return RouterEntry[]
     */
    public function findByRouterKey($routerKey);

    /**
     * @param string $routerKey
     * @param int $page
     * @param int $pageSize
     * @return RouterEntry
     */
    public function findRouterEntryListByRouterKeyAndPageAndPageSize(
        $routerKey,
        $page,
        $pageSize
    );

    /**
     * @param string $routerKey
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry($routerKey, RouterEntry $routerEntry);
}