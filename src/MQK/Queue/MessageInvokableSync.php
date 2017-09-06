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
    protected $numberOfGroup;

    /**
     * @var Queue
     */
    protected $queue;

    public function __construct($groupId, $numberOfGroup, $id, $discriminator = "invokable_sync", $queue = null, $ttl = 600, $payload = null)
    {
        $this->groupId = $groupId;
        $this->numberOfGroup = $numberOfGroup;
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

    public function __invoke()
    {
        $result = parent::__invoke();

        $redis = RedisFactory::shared()->createRedis();

        // 如果是一条消息直接通知结束。
        // 如果是多条消息，进入流程。

        if ($this->invokeNumbers > 1) {
//            $group = $redis->hGet("group:{$this->groupId}");
//
//            if (count($group->completed) >= $this->invokeNumbers) {
//                $queue->enqueue($message);
//            }
        } else {
            // 只有一条同步调用时节省一次kv的访问
            $message = null;

            $this->logger->debug("只有一条消息，通知客户端返回结果。");
            $queue->enqueue($message);
        }

        return $result;
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
        $json['numberOfGroup'] = $this->numberOfGroup;
        return $json;
    }
}