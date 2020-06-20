<?php

namespace Polygen\Utils;

/**
 * Trait for objects that should not be serialized or unserialized through the standard PHP methods, because they are
 * singletons or (kind of flyweights), and unserializing will make strict object equality checks fail for them.
 */
trait Unserializable
{
    public final function __wakeup()
    {
        throw new \RuntimeException(sprintf('%s can\'t be unserialized directly.', get_called_class()));
    }

    public final function __sleep()
    {
        throw new \RuntimeException(sprintf('%s can\'t be serialized directly.', get_called_class()));
    }
}
