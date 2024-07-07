<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonSerializer implements SerializerInterface
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function serialize(mixed $object): string
    {
        return $this->serializer->serialize($object, 'json');
    }

    public function deserialize(mixed $data, string $className): mixed
    {
        return $this->serializer->deserialize($data, $className, 'json');
    }
}
