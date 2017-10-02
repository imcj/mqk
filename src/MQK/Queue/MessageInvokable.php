<?php
namespace MQK\Queue;

use Symfony\Component\Serializer\Serializer;
use MQK\SerializerFactory;

class MessageInvokable extends Message
{
    /**
     * 方法名
     *
     * @var string
     */
    protected $func;

    /**
     * 方法调用参数
     *
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $returns;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * MessageInvokable constructor.
     *
     * @param string $id
     * @param string $discriminator
     * @param string|mull $queue
     * @param int $ttl
     * @param array|null $payload
     */
    public function __construct($id, $discriminator = "invokable", $queue = null, $ttl = 600, $payload = null)
    {
        parent::__construct($id, $discriminator, $queue, $ttl, $payload);

        if (null != $payload) {
            $this->setPayload($payload);
        }
        $this->serializer = SerializerFactory::shared()->serializer();

        $className = explode("::", $this->func)[0];
        if (property_exists($className, "queue")) {
            $this->queue = $className::$queue;
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function __invoke()
    {
        $arguments = $this->arguments;
        try {
            $returns = call_user_func_array($this->func, $arguments);

            $error = error_get_last();
            error_clear_last();
            if ($error) {
                if (property_exists($error, 'file')) {
                    if (!strpos("Php70.php", $error->file) > -1) {
                        $e = new \Exception($error['message'], $error['type']);
                        throw $e;
                    }
                }
            }

        } catch (\Exception $e) {
            throw $e;
        }

        $this->returns = $returns;
        return $returns;
    }

    public function returns()
    {
        return $this->returns;
    }

    public function setReturns($returns)
    {
        $this->returns = $returns;
    }

    public function setPayload($payload)
    {
        if (property_exists($payload, 'func')) {
            $this->func = $payload->func;
            $this->arguments = $payload->arguments;
        }
        parent::setPayload($payload);
    }

    public function jsonSerialize()
    {
        $payload = array(
            'func' => $this->func,
            'arguments' => $this->arguments
        );
        $json = parent::jsonSerialize();
        $json['payload'] = $payload;

        if (null != $this->returns) {
            $json['returns'] = $this->returns;
        }

        return $json;
    }
}