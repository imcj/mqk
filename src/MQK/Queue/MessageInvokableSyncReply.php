<?php
namespace MQK\Queue;


class MessageInvokableSyncReply extends Message
{
    /**
     * @var MessageInvokableSync[]
     */
    protected $invokes;

    protected $groupId;

    protected $numberOfInvoke;

    public function __construct(
        $invokes,
        $groupId,
        $queue = null,
        $ttl = 600,
        $payload = null
    ) {
        $this->invokes = $invokes;
        $this->groupId = $groupId;

        parent::__construct($groupId, "invokable_sync_reply", $queue, $ttl, $payload);
    }

    /**
     * @return MessageInvokableSync
     */
    public function invokes()
    {
        return $this->invokes;
    }

    public function firstInvoke()
    {
        return $this->invokes[0];
    }

    public function groupId()
    {
        return $this->groupId;
    }

    public function jsonSerialize()
    {

        $json = parent::jsonSerialize();
        $json['groupId'] = $this->groupId;
        $json['invokes'] = $this->invokes;
//        $json['numberOfInvoke'] = $this->invoke->numberOfInvoke();

        return $json;
    }
}