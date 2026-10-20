<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderMessageLevelEnum: string
{
    case ADVANTAGE = 'advantage';
    case INFO = 'info';
    case ALERT = 'alert';
}
