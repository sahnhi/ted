<?php

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\ConversionException;

/**
 * Type that maps a PHP array to a clob SQL type.
 */
class OrderedArrayType extends ArrayType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $value = \is_resource($value) ? \stream_get_contents($value) : $value;

        \set_error_handler(function (int $code, string $message): bool {
            throw ConversionException::conversionFailedUnserialization($this->getName(), $message);
        });

        try {
            $value = \unserialize($value);
            asort($value);

            return $value;
        } finally {
            \restore_error_handler();
        }
    }

    public function getName()
    {
        return 'ordered_array';
    }
}
