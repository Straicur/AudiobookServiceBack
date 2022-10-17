<?php

namespace App\Serializer;

/**
 * SerializerInterface
 *
 */
interface SerializerInterface
{
    public function serialize(mixed $object): string;

    public function deserialize(mixed $data, string $className): mixed;
}