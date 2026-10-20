<?php

declare(strict_types=1);

namespace App\Services\Review\Rss;

class RssReviewBackupFilesException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Error during the backup of old reviews');
    }
}
