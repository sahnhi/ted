<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestCase extends KernelTestCase
{
    public const LOCALE_CODE = 'en_GB';
    public const LOCALE_CODE_FR = 'fr_FR';

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     *
     * @throws \ReflectionException
     */
    public function invokeMethod(object &$object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Sets a protected property on a given object via reflection.
     *
     * @param $object   - instance in which protected value is being modified
     * @param $property - property on instance being modified
     * @param $value    - new value of the property being modified
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    public function getTranslator(): TranslatorInterface
    {
        if (!static::$booted) {
            static::bootKernel();
        }

        return self::$container->get('translator');
    }

    public function getService(string $aliasService): object
    {
        if (!static::$booted) {
            static::bootKernel();
        }

        return self::$container->get($aliasService);
    }
}
