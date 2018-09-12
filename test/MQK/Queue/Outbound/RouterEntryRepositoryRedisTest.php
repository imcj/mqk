<?php
namespace MQK\Queue\Outbound;


use MQK\Queue\Outbound\Redis\RouterEntryRepositoryRedis;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RouterEntryRepositoryRedisTest extends TestCase
{
    public function testCRUD()
    {
        $client = new Client([
            'host' => '127.0.0.1'
        ]);

        $repository = new RouterEntryRepositoryRedis($client);

        $routerEntry = new RouterEntry(
            -1,
            'test',
            'test',
            'test',
            new \DateTime(),
            new \DateTime()
        );

        $repository->addNewRouterEntry('test', $routerEntry);
        $routerEntryList = $repository->findByRouterKey('test');
    }
}