<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Message\DTO\OrderDTO;
use App\Services\Mail\TemplateVar\TemplateVarsInterface;

final class MandrillEmailSender implements EmailSenderInterface
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function send(OrderDTO $order, TemplateVarsInterface $templateVars, string $mergeLanguage): void
    {
        $this->mandrillEmail->generateEmail($order, $templateVars->getTemplateName(), $templateVars->toArray(), MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }
}
