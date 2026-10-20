<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Trait\Order;

use App\Model\Order\OrderMessageInterface;

trait OrderMessagesTrait
{
    private iterable $messages = [];

    public function getMessages(): iterable
    {
        return $this->messages;
    }

    public function addMessage(OrderMessageInterface $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    public function hasMessages(): bool
    {
        return count($this->messages) > 0;
    }

    public function clearMessages(): self
    {
        $this->messages = [];

        return $this;
    }
}
