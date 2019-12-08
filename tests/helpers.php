<?php

function get_protected_property($object, $key)
{
    $reflection = new ReflectionClass($object);

    $property = $reflection->getProperty($key);

    $property->setAccessible(true);

    return $property->getValue($object);
}
