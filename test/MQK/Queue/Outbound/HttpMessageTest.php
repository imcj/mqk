<?php
namespace MQK\Queue\Outbound;


use MQK\Queue\Outbound\Command\CreateRouterEntryCommand;
use MQK\Queue\Outbound\Impl\NotificationCenterImpl;
use MQK\Queue\Outbound\Impl\OutboundServiceFacadeImpl;
use MQK\Queue\Outbound\Impl\OutboundServiceImpl;
use PHPUnit\Framework\TestCase;

class HttpMessageTest extends TestCase
{
    public function testA()
    {
        $routerEntryCommand = new CreateRouterEntryCommand(
            'order.created',
            'http://127.0.0.1:8000',
            'that description'
        );

        $routerEntry = new RouterEntry(
                    1,

                    "order.created",
                    "http://127.0.0.1:8000",
                    "",
                    new \DateTime(),
                    new \DateTime()
                );

        $notificationCenter = \Mockery::mock(NotificationCenterImpl::class)
            ->makePartial();

        $notificationCenter
            ->shouldReceive('notify')
            ->withAnyArgs()
            ->times(1)
            ->andReturnNull();

        $repository = $this->getMockBuilder(\MQK\Queue\Outbound\Redis\RouterEntryRepositoryRedis::class)
            ->getMock();

        $repository->method('findByRouterKey')
            ->willReturn([$routerEntry]);

        $service = new OutboundServiceImpl($repository, $notificationCenter);

        $serviceFacade = new OutboundServiceFacadeImpl(
            $service
        );
        $serviceFacade->addNewRouterEntry($routerEntryCommand);

        $message = null;
        $serviceFacade->launch("order.created", $message);

        // except remote called

    }
}