<?php

declare(strict_types=1);

namespace App\Channable\Entity;

interface ChannableFileLogStatesInterface
{
    public const STATE_CURRENT = 'current';
    public const STATE_QUEUED = 'queued';
    public const STATE_ERROR = 'error';
    public const STATE_PROCESSING = 'processing';
    public const STATE_OLD = 'old';
}
