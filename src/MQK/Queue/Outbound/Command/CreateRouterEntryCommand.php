<?php
namespace MQK\Queue\Outbound\Command;


class CreateRouterEntryCommand
{
    /**
     * @var string
     */
    private $routerKey;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $description;

    public function __construct($routerKey, $endpoint, $description)
    {
        $this->routerKey = $routerKey;
        $this->endpoint = $endpoint;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function routerKey()
    {
        return $this->routerKey;
    }

    /**
     * @param string $routerKey
     */
    public function setRouterKey($routerKey)
    {
        $this->routerKey = $routerKey;
    }

    /**
     * @return string
     */
    public function endpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


}