<?php
namespace MQK\Queue;


class MessageInvokableSync extends MessageInvokable
{
    private $groupId;

    public function __construct($groupId, $id, $queue = null, $ttl = 600, $payload = null)
    {
        $this->groupId = $groupId;
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

        return $result;
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['groupId'] = $this->groupId;
        return $json;
    }
}