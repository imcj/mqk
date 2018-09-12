<?php
namespace MQK\Queue\Outbound;


class RouterEntry implements \JsonSerializable
{
    /**
     * @var int
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

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    public function __construct(
        $id,
        $routerKey,
        $endpoint,
        $description,
        $createdAt,
        $updatedAt
    ) {

        $this->id = $id;
        $this->routerKey = $routerKey;
        $this->endpoint = $endpoint;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param int $id
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

    /**
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function updatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
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
            'id' => (int)$this->id,
            'description' => $this->description,
            'router_key' => $this->routerKey,
            'endpoint' => $this->endpoint,
//            'created_at' => $this->createdAt,
//            'updated_at' => $this->updatedAt
        ];
    }
}