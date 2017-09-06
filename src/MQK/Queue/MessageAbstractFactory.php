<?php
namespace MQK\Queue;

use MQK\CaseConverion;
use MQK\SerializerFactory;
use MQK\SingletonTrait;
use phpDocumentor\Reflection\DocBlock\Serializer;

class MessageAbstractFactory
{
    /**
     * Hash map of message factory cached
     * @var array
     */
    private $messageFactories = [];

    use SingletonTrait;

    /**
     * @param object $messageJson
     * @return Message
     */
    public function messageWithJson($messageJson)
    {
        $discriminator = "";
        if (!property_exists($messageJson, "discriminator")) {
            $messageJson->discriminator = "invokable";
        } else {
            $discriminator = $messageJson->discriminator;
        }

        $messageFactoryClassName = CaseConverion::snakeToCamel($discriminator) . "Factory";
        $messageFactoryClass = "\\MQK\\Queue\\MessageFactory\\Message{$messageFactoryClassName}";
        $messageFactoryInstance = null;

        if (!isset($this->messageFactories[$messageFactoryClass])) {
            $messageFactoryInstance = new $messageFactoryClass();
            $this->messageFactories[$messageFactoryClass] = $messageFactoryInstance;
        } else {
            $messageFactoryInstance = $this->messageFactories[$messageFactoryClass];
        }

        $message = $messageFactoryInstance->withJsonObject($messageJson);


        if (property_exists($messageJson, "retries")) {
            $message->setRetries($messageJson->retries);
        }

        return $message;
    }

    /**
     * 派发事件时使用messageWithEvent构造Message对象用于消息入列
     *
     * @param $event
     * @return MessageEvent
     */
    public function messageWithEvent($event)
    {
        /**
         * @var $serializer \Symfony\Component\Serializer\Serializer
         */
        $serializer = SerializerFactory::shared()->serializer();

        $message = new MessageEvent(uniqid(), "event");
        $payload = new \stdClass();

        $eventClass = get_class($event);
        $payload->eventName = $eventClass::NAME;
        $payload->className = $eventClass;
        $payload->serialized = $serializer->normalize($event, 'json');

        $message->setPayload($payload);
        return $message;
    }
}