<?php

declare(strict_types=1);

namespace App\Services\Review\Rss;

class RssReviewXmlFilesNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Review rss xml files not found');
    }
}
