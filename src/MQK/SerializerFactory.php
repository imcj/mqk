<?php
namespace MQK;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    use SingletonTrait;

    /**
     * @return Serializer
     */
    public function serializer()
    {
        $serializer = new Serializer(
            [new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())],
            [new JsonEncoder()]
        );

        return $serializer;
    }
}