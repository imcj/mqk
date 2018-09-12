<?php
namespace MQK\Queue\Outbound\Redis;


use MQK\Queue\Outbound\RouterEntry;
use MQK\Queue\Outbound\RouterEntryRepository;
use Predis\Client;

class RouterEntryRepositoryRedis implements RouterEntryRepository
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

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
    ) {

    }

    /**
     * @param string $routerKey
     * @return RouterEntry[]
     */
    public function findByRouterKey($routerKey)
    {
        $serializedList = $this->client->zrange("router_entry_${routerKey}_index", 0, -1);
        return array_map(function ($serialized) {
            $deserialized = json_decode($serialized, true);
            return $this->assembleToModel($deserialized);
        }, $serializedList);
    }

    /**
     * @param string $routerKey
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry($routerKey, RouterEntry $routerEntry)
    {

        $id = $this->client->incr("router_entry_index_counter");
        $routerEntry->setId($id);
        $serialized = json_encode($routerEntry->jsonSerialize());
        $this->client->hmset("router_entry_${routerKey}_" . $routerEntry->id(), $routerEntry->jsonSerialize());
        $this->client->zAdd("router_entry_${routerKey}_index", $routerEntry->id(), $serialized);

        return $routerEntry;
    }

    protected function assembleToModel($serialized)
    {
        return new RouterEntry(
            (int)$serialized['id'],
            $serialized['router_key'],
            $serialized['endpoint'],
            $serialized['description'],
            null, // new \DateTime($serialized['created_at']),
            null // new \DateTime($serialized['updated_at'])
        );
    }
}