<?php
namespace MQK;

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
    private $host;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct($host, $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;

        $this->connection = new \Redis();
    }

    public function connect()
    {
        $this->connection->connect($this->host, $this->port);
    }

    public function auth($password)
    {
        $this->connection = $password;
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
                    $this->redisFactory->reconnect(3);
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