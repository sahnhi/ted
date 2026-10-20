<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\AdminParameterRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'admin_parameter')]
#[ORM\Entity(repositoryClass: AdminParameterRepository::class)]
class AdminParameter implements ResourceInterface
{
    public const DAYS_DELAY_REVIEW_KEY = 'days_delay_review';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'key', type: 'string')]
    private ?string $key = null;

    #[ORM\Column(name: 'value', type: 'string')]
    private ?string $value = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): AdminParameter
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): AdminParameter
    {
        $this->value = $value;

        return $this;
    }
}
