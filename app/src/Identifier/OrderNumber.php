<?php

declare(strict_types=1);

namespace App\Identifier;

final class OrderNumber
{
    private function __construct(
        private readonly string $orderNumber,
        private readonly string $deprecatedOrderNumber
    ) {
    }

    public static function create(string $number): self
    {
        return new self($number, $number);
    }

    public static function fromSpidy(string $number): self
    {
        // Get Base Order Number (EX TED25002RB1 => 25002)
        $matches = [];
        preg_match("/([0-9]+)(\d{3})/", $number, $matches);
        $base = !empty($matches) ? $matches[0] : $number;

        // Get Everything after Base Order Number to get full number (EX TED25002RB1 => RB1 then TED25002RB1 => 00025002RB1)
        $suffix = substr($number, strpos($number, $base) + strlen($base));
        $number = str_pad($base, 9, '0', STR_PAD_LEFT) . $suffix;

        return new self($number, $base . $suffix);
    }

    public static function completingWith0AtTheBeginning(string $partialNumber): self
    {
        return new self(str_pad($partialNumber, 9, '0', STR_PAD_LEFT), str_pad($partialNumber, 9, '0', STR_PAD_LEFT));
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getDeprecatedOrderNumber(): string
    {
        return $this->deprecatedOrderNumber;
    }
}
