<?php
namespace MQK\Queue;


use GuzzleHttp\Promise\Promise;
use MQK\RedisFactory;

class MessageInvokableSync extends MessageInvokable
{
    private $groupId;

    /**
     * @var integer
     */
    protected $numberOfInvoke;

    /**
     * @var Queue
     */
    protected $queue;

    public function __construct($groupId, $numberOfInvoke, $id, $discriminator = "invokable_sync", $queue = null, $ttl = 600, $payload = null)
    {
        $this->groupId = $groupId;
        $this->numberOfInvoke = $numberOfInvoke;
        parent::__construct($id, "invokable_sync", $queue, $ttl, $payload);
    }

    public function groupId()
    {
        return $this->groupId;
    }

    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    public function numberOfInvoke()
    {
        return $this->numberOfInvoke;
    }

    public function watch($handler)
    {
        while (true) {
            list($queueName, $messageJson) = $this->connection->blpop($this->groupId, 10);
            if (empty($messageJson)) {
                continue;
            }

            $this->connection->delete();
            $handler($messageJson);
            breka;
        }
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['groupId'] = $this->groupId;
        $json['numberOfInvoke'] = $this->numberOfInvoke;

        return $json;
    }
}