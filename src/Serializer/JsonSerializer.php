<?php

declare(strict_types = 1);

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use const JSON_UNESCAPED_UNICODE;

class JsonSerializer implements SerializerInterface
{
    private readonly Serializer $serializer;

    public function __construct()
    {
        $defaultContext = [
            JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE,
        ];

        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder(new JsonEncode($defaultContext))]);
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
