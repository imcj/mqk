<?php
namespace MQK;

use AD7six\Dsn\Dsn;
use MQK\Exception\EmptyQueueException;

class RedisProxy
{
    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var RedisFactory
     */
    private $factory;

    private $retries = 0;

    private $max = 3;

    private $logger;

    public function __construct($dsn)
    {
        $this->dsn = $dsn;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function connect($renew = false)
    {
        if (null == $this->connection) {
            $this->connection = new \Redis();
        }
        $dsn = Dsn::parse($this->dsn);
        $this->connection->connect($dsn->host, $dsn->port);
        $this->connection->auth($dsn->password);
        assert("+PONG" == $this->connection->ping());
    }

    /**
     * @param bool $block
     * @return \stdClass
     * @throws \Exception
     * @throws \RedisException
     */
    public function listPop($queueKeys, $block = true, $timeout = 1)
    {
        for ($i = 0; $i < 3; $i++) {
            try {
                if ($block) {
                    $raw = $this->connection->blPop($queueKeys, $timeout);
                    if (!$raw)
                        return null;
                } else {
                    foreach ($queueKeys as $queueKey) {
                        $raw = $this->connection->lPop($queueKey);
                        if ($raw) {
                            $raw = array($queueKey, $raw);
                            break;
                        } else {
                            throw new EmptyQueueException(null);
                        }
                    }
                }
                break;
            } catch (\RedisException $e) {
                // e 0
                // read error on connection
                $this->logger->error($e->getCode());
                $this->logger->error($e->getMessage());
                if ("read error on connection" == $e->getMessage()) {
                    $this->retires += 1;
                    $this->logger->info("Redis retry {$this->retires} times.");
                    if ($this->retires >= $this->max) {
                        $this->logger->info("Max retries {$this->max} will be quit.");
                        sleep(1);
                        exit(0);
                    }

                    $this->connect(true);
                    continue;
                }

                throw $e;
            }
        }
        if (count($raw) < 2) {
            throw new \Exception("queue data count less 2.");
        }
        list($queueKey, $messageJson) = $raw;

        if (empty($messageJson))
            return null;

        return $messageJson;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->connection, $name), $arguments);
    }
}