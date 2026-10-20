<?php

declare(strict_types=1);

namespace App\ERP\Domain\StateMachine\Applicator\Exception;

final class TransitionFailException extends \DomainException implements \Throwable
{
}
