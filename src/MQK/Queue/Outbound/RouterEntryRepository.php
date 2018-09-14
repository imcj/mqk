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
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry(RouterEntry $routerEntry);

    /**
     * @param string $routerKey
     * @param int $id
     * @return RouterEntry
     */
    public function findByRouterKeyAndId($routerKey, $id);

    /**
     * @param string $routerKey
     * @param int $id
     */
    public function removeRouterKeyAndId($routerKey, $id);
}