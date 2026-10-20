<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'counter')]
#[ORM\Entity]
class Counter
{
    public const CLIMATE_KEY = 'climate';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: '`key`', type: 'string', nullable: false)]
    private ?string $key = null;

    #[ORM\Column(name: 'year', type: 'integer', nullable: false)]
    private ?int $year = null;

    #[ORM\Column(name: 'counter', type: 'integer', nullable: false)]
    private ?int $counter = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Counter
    {
        $this->id = $id;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): Counter
    {
        $this->key = $key;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): Counter
    {
        $this->year = $year;

        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): Counter
    {
        $this->counter = $counter;

        return $this;
    }

    public function incrementCounter(int $value = 1): Counter
    {
        $this->counter += $value;

        return $this;
    }
}
