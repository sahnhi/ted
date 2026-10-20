<?php

namespace App\Model\GiftCard;

final class GiftCardPdf
{
    public function __construct(
        private readonly string $filename,
        private readonly string $content
    ) {
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function content(): string
    {
        return $this->content;
    }
}
