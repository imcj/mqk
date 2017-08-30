<?php

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    public function testSerializer()
    {
        $o = new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(null, new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter());
        $c = new \MQK\Queue\ComplexEvent(1);
        $nor = [new \Symfony\Component\Serializer\Normalizer\PropertyNormalizer(null, new \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter())];
        $encoder = [new \Symfony\Component\Serializer\Encoder\JsonEncoder()];
        $serializer = new \Symfony\Component\Serializer\Serializer([$o], $encoder);

        $c->child = new \MQK\Queue\ComplexEvent(2);

        $cj = $serializer->serialize($c, "json");
        $cj2 = $serializer->normalize($c);

//        $c->child->parent = $c;

        $type = \MQK\Queue\ComplexEvent::class;
        $c3 = $serializer->denormalize($cj2, $type);
        $c4 = $serializer->serialize($c, "json");
        $c5 = $serializer->deserialize($c4,  $type,"json");
        assert(true);
    }
}