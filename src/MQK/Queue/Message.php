<?php
namespace MQK\Queue;


class Message implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * 消息的超时时间
     *
     * @var int
     */
    protected $ttl;

    /**
     * 消息所在的队列名
     *
     * @var string
     */
    protected $queue;

    /**
     * Current retry times
     * @var int
     */
    protected $retries;

    /**
     * @var object
     */
    protected $payload;

    /**
     * 多态类型
     * @var string
     */
    protected $discriminator;

    public function __construct($id, $discriminator = "invokable", $queue = null, $ttl = 600, $payload = null)
    {
        $this->id = $id;
        $this->queue = $queue;
        $this->ttl = $ttl;
        $this->payload = $payload;
        $this->retries = 0;
        $this->discriminator = $discriminator;
    }

    public function discriminator()
    {
        return $this->discriminator;
    }

    public function id()
    {
        return $this->id;
    }

    public function ttl()
    {
        return $this->ttl;
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    public function retries()
    {
        return $this->retries;
    }

    public function setRetries($retries)
    {
        $this->retries = $retries;
    }

    public function queue()
    {
        return $this->queue;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function payload()
    {
        return $this->payload;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $json = [];
        $keys = ['id', 'ttl', 'queue', 'retries', 'discriminator'];
        foreach ($keys as $key) {
            $json[$key] = $this->$key;
        }
        return $json;
    }

    public function __invoke()
    {
        throw new \Exception("Not implemented __invoke");
    }
}