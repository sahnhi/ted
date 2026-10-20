<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Message\DTO\OrderDTO;
use App\Services\Mail\TemplateVar\TemplateVarsInterface;

interface EmailSenderInterface
{
    public function send(OrderDTO $order, TemplateVarsInterface $templateVars, string $mergeLanguage): void;
}
