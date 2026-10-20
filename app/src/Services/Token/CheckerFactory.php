<?php

namespace App\Services\Token;

use App\Services\Token\Checker\Autonomous;
use App\Services\Token\Checker\NotFoundException;

class CheckerFactory
{
    /** @var AbstractChecker[] */
    private array $checkers;

    public function __construct(
        private readonly Autonomous $autonomous
    ) {
        $this->checkers = [
            Autonomous::class => $this->autonomous,
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function get(string $checker): AbstractChecker
    {
        if (!array_key_exists($checker, $this->checkers)) {
            throw new NotFoundException($checker);
        }

        return $this->checkers[$checker];
    }
}
