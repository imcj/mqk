<?php
namespace MQK\Queue;

use MQK\SerializerFactory;
use MQK\SingletonTrait;
use phpDocumentor\Reflection\DocBlock\Serializer;

class MessageFactory
{
    use SingletonTrait;

    /**
     * @param object $messageJson
     * @return Message
     */
    public function messageWithJson($messageJson)
    {
        $discriminator = "";
        if (property_exists($messageJson, "discriminator")) {
            $discriminator = $messageJson->discriminator;
        } else {
            $discriminator = "invokable";
        }

        switch ($discriminator) {
            case "invokable":
                $messageClass = MessageInvokable::class;
                break;
            default:
                $messageClass = MessageEvent::class;
                break;
        }



        $message = new $messageClass(
            $messageJson->id,
            $discriminator,
            $messageJson->queue,
            $messageJson->ttl,
            $messageJson->payload
        );

        if (property_exists($messageJson, "retries")) {
            $message->setRetries($messageJson->retries);
        }

        return $message;
    }

    public function messageWithEvent($event)
    {
        /**
         * @var $serializer \Symfony\Component\Serializer\Serializer
         */
        $serializer = SerializerFactory::shared()->serializer();

        $message = new MessageEvent(uniqid(), "message_event");
        $payload = new \stdClass();

        $eventClass = get_class($event);
        $payload->eventName = $eventClass::NAME;
        $payload->className = $eventClass;
        $payload->serialized = $serializer->normalize($event, 'json');

        $message->setPayload($payload);
        return $message;
    }
}