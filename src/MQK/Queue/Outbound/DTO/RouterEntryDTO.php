<?php
namespace MQK\Queue\Outbound\DTO;


class RouterEntryDTO implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $routerKey;

    /**
     * @var string
     */
    private $endpoint;

    public function __construct($id, $routerKey, $endpoint)
    {
        $this->id = $id;
        $this->routerKey = $routerKey;
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'router_key' => $this->routerKey,
            'endpoint' => $this->endpoint
        ];
    }
}