<?php

declare(strict_types=1);

namespace App\Analytic\Model;

use IteratorAggregate;

/** @implements IteratorAggregate<User> */
final class UsersList implements \Countable, \IteratorAggregate
{
    public function __construct(
        /** @var User[] */
        private array $users
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function addToList(User $user): self
    {
        $users = $this->users;
        $users[] = $user;

        return new self($users);
    }

    public function count(): int
    {
        return count($this->users);
    }

    /**
     * @return \Generator<User>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->users;
    }

    /** @return User[] */
    public function getUsers(): array
    {
        return $this->users;
    }

    /** @return string[] */
    public function getUserIds(): array
    {
        $userIds = [];
        foreach ($this->users as $user) {
            $userIds[] = $user->getId();
        }

        return $userIds;
    }
}
