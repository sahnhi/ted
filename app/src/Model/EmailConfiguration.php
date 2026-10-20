<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\EmailConfigurationName;

final class EmailConfiguration
{
    public function __construct(
        public readonly EmailConfigurationName $emailConfigurationName,
        public readonly array $principals,
        public readonly ?string $from = null,
        public readonly ?string $fromName = null,
        public readonly ?array $ccs = null,
    ) {
    }

    public function getEmailConfigurationName(): EmailConfigurationName
    {
        return $this->emailConfigurationName;
    }

    public function getPrincipals(): array
    {
        return $this->principals;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function getCcs(): ?array
    {
        return $this->ccs;
    }
}
