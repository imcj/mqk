<?php
namespace MQK\Queue;


use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\SerializerFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MessageEvent extends Message
{
    protected static $bus;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Logger
     */
    protected $logger;


    public function __construct($id, $discriminator = "invokable", $queue = null, $ttl = 600, $payload = null)
    {
        $this->serializer = SerializerFactory::shared()->serializer();
        parent::__construct($id, $discriminator, $queue, $ttl, $payload);
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function __invoke()
    {
        if (self::$bus == null) {
            self::$bus = new EventDispatcher();
        }

        $event = $this->event();

        $this->logger->debug("Dispatch event {$this->payload->eventName}");
        MessageEventBus::shared()->dispatch($this->payload->eventName, $event);
    }

    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $event = $this->event();
        $payload = array(
            "className" => get_class($event),
            'eventName' => $this->payload->eventName,
            'serialized' => $this->serializer->normalize($event)
        );
        $json['payload'] = $payload;
        return $json;
    }

    function event()
    {
        $className = $this->payload->className;
        $serialized = $this->payload->serialized;

        $event = $this->serializer->denormalize($serialized, $className);
        return $event;
    }
}