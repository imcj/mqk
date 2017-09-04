<?php
namespace MQK\Queue;

use Symfony\Component\Serializer\Serializer;
use MQK\SerializerFactory;

class MessageInvokable extends Message
{
    private $func;
    private $arguments;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($id, $discriminator = "invokable", $queue = null, $ttl = 600, $payload = null)
    {
        parent::__construct($id, $discriminator, $queue, $ttl, $payload);
        var_dump($payload);

        $this->func = $payload->func;
        $this->arguments = $payload->arguments;
        $this->serializer = SerializerFactory::shared()->serializer();
    }

    public function __invoke()
    {
        $arguments = json_decode($this->arguments);
        $result = @call_user_func_array($this->func, $arguments);

        $error = error_get_last();
        error_clear_last();
//        if (!empty($error)) {
//            $this->logger->error($error['message']);
//            $this->logger->error($this->func());
//            $this->logger->error(json_encode($this->arguments()));
//
//            throw new \Exception($error['message']);
//        }
    }

    public function jsonSerialize()
    {
        $payload = array(
            'func' => $this->func,
            'arguments' => json_encode($this->arguments)
        );
        $json = parent::jsonSerialize();
        $json['payload'] = $payload;

        return $json;
    }
}