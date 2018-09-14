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

    /**
     * @var string
     */
    private $routerEntryKey = "router_entry_%s_%d";

    /**
     * @var string
     */
    private $routerEntryIndexKey = "router_entry_%s_index";

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
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry(RouterEntry $routerEntry)
    {
        $routerKey = $routerEntry->routerKey();
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

    public function removeRouterKeyAndId($routerKey, $id)
    {
        $this->client->del([
            sprintf(
                $this->routerEntryKey,
                $routerKey,
                $id
            )
        ]);
        $this->client->zremrangebyscore(
            sprintf($this->routerEntryIndexKey, $routerKey),
            $id,
            "{$id}"
        );
    }

    /**
     * @param string $routerKey
     * @param int $id
     * @return RouterEntry
     */
    public function findByRouterKeyAndId($routerKey, $id)
    {
        return $this->assembleToModel(
            $this->client->hgetall(
                sprintf($this->routerEntryKey, $routerKey, (int)$id)
            )
        );
    }
}