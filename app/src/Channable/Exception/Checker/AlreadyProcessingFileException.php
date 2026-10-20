<?php

declare(strict_types=1);

namespace App\Channable\Exception\Checker;

final class AlreadyProcessingFileException extends \RuntimeException
{
    private const MESSAGE = 'app.ui.channable.error.already_processing_file';

    public function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }
}
