<?php

declare(strict_types=1);

namespace App\Component\Generator;

use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;

final class RandomnessGenerator implements RandomnessGeneratorInterface
{
    private readonly string $uriSafeAlphabet;
    private readonly string $digits;

    public function __construct()
    {
        $this->digits = implode('', range(0, 9));

        /* Custom Code */
        $this->uriSafeAlphabet =
            implode('', range(0, 9))
            . implode('', range('a', 'z'))
            . implode('', range('A', 'Z'))
            . implode('', ['-', '_'])
        ;
        /* End Custom Code */
    }

    public function generateUriSafeString(int $length): string
    {
        return $this->generateStringOfLength($length, $this->uriSafeAlphabet);
    }

    public function generateNumeric(int $length): string
    {
        return $this->generateStringOfLength($length, $this->digits);
    }

    public function generateInt(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    private function generateStringOfLength(int $length, string $alphabet): string
    {
        $alphabetMaxIndex = strlen($alphabet) - 1;
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $index = random_int(0, $alphabetMaxIndex);
            $randomString .= $alphabet[$index];
        }

        return $randomString;
    }
}
