<?php
namespace MQK\Queue\Outbound;


use MQK\Config;
use MQK\Queue\RedisHelper;
use Psr\Container\ContainerInterface;
use Slim\App;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Predis\Client;
use MQK\Queue\Outbound\Impl\OutboundServiceFacadeImpl;
use MQK\Queue\Outbound\Impl\OutboundServiceImpl;
use MQK\Queue\Outbound\RouterEntryRepository;
use MQK\Queue\Outbound\Redis\RouterEntryRepositoryRedis;
use MQK\Queue\Outbound\RouterEntry;
use MQK\Queue\Outbound\Impl\NotificationCenterImpl;
use MQK\Queue\Message;
use MQK\Queue\MessageNormal;
use MQK\Queue\RedisQueue;
use MQK\RedisProxy;
use MQK\Queue\Outbound\Command\CreateRouterEntryCommand;

class OutboundRouter
{
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->dependenciesInject($app->getContainer());

        $app->get('/router/{router_key}/entry', function (Request $request, Response $response, array $args) {
            $routerKey = $args['router_key'];
            $outboundServiceFacade = $this->get(OutboundServiceFacade::class);

            $routerEntryDTOList = $outboundServiceFacade
                ->listRouterEntry($routerKey, 100);

            return $response->withJson($routerEntryDTOList);
        });

        $app->post('/router/{router_key}/entry', function (Request $request, Response $response, array $args) {
            $routerKey = $args['router_key'];
            /**
             * @var OutboundServiceFacade
             */
            $outboundServiceFacade = $this->get(OutboundServiceFacade::class);

            $input = $request->getParsedBody();

            return $response->withJson(
                $outboundServiceFacade->addNewRouterEntry(
                    new CreateRouterEntryCommand(
                        $routerKey,
                        $input['endpoint'],
                        $input['description']
                    )
                )
            );
        });

        $app->get('/router/{router_key}/entry/{id}', function (Request $request, Response $response, array $args) {
            $routerKey = $args['router_key'];
            $routerEntryId = (int)$args['id'];

            $client = $this->get(Client::class);

            $routerEntryRepository = new RouterEntryRepositoryRedis($client);
            $routerEntry = $routerEntryRepository->findByRouterKeyAndId($routerKey, $routerEntryId);

            return $response->withJson($routerEntry->jsonSerialize());
        });

        $app->delete('/router/{router_key}/entry/{id}', function (Request $request, Response $response, array $args) {
            $routerKey = $args['router_key'];
            $routerEntryId = (int)$args['id'];


            /**
             * @var RouterEntryRepository
             */
            $routerEntryRepository = $this->get(RouterEntryRepository::class);
            $routerEntry = $routerEntryRepository->findByRouterKeyAndId($routerKey, $routerEntryId);
            $routerEntryRepository->removeRouterKeyAndId(
                $routerKey, $routerEntryId
            );

            return $response->withJson($routerEntry->jsonSerialize());
        });

        $app->post('/{queue_name}/message/{router_key}', function (Request $request, Response $response, array $args) {
            $routerKey = $args['router_key'];
            $queueName = $args['queue_name'];

            $body = $request->getBody()->getContents();
            $payload = json_decode($body);

            $message = new MessageNormal(uniqid(), 'normal', $queueName, 600, $payload);
            $message->setRouterKey($routerKey);

            $client = $this->get(Client::class);

            $proxy = new RedisProxy("");
            $proxy->setConnection($client);
            $queue = new RedisQueue($proxy);

            $queue->enqueue($queueName, $message);

            return $response->withJson($message);

        });
    }

    public function dependenciesInject(ContainerInterface $container)
    {
        $container[Client::class] = function ($container) {

            $redisDsn = Config::defaultConfig()->redis();
            $redisHelper = new RedisHelper();
            $options = $redisHelper->dsnToRedis($redisDsn);
            return new Client([
                'host' => $options['host'],
                'port' => (int)$options['port'],
                'password' => $options['password'],
                'database' => $options['database']
            ]);
        };

        $container[RouterEntryRepository::class] = function(ContainerInterface $container) {
            return new RouterEntryRepositoryRedis($container->get(Client::class));
        };

        $container[NotificationCenter::class] = function(ContainerInterface $container) {
            return new NotificationCenterImpl(new \GuzzleHttp\Client());
        };

        $container[OutboundService::class] = function (ContainerInterface $container) {
            return new OutboundServiceImpl(
                $container->get(RouterEntryRepository::class),
                $container->get(NotificationCenter::class)
            );
        };

        $container[OutboundServiceFacade::class] = function (ContainerInterface $container) {
            return new OutboundServiceFacadeImpl(
                $container->get(RouterEntryRepository::class),
                $container->get(OutboundService::class)
            );
        };
    }

    /**
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function boot()
    {
        $this->app->run();
    }
}