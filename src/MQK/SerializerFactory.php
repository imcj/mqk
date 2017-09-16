<?php
namespace MQK;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    use SingletonTrait;

    /**
     * @return Serializer
     */
    public function serializer()
    {
        $propertyNormalizer = new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        // new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())
        $serializer = new Serializer(
            [$propertyNormalizer],
            [new JsonEncoder()]
        );

        return $serializer;
    }
}