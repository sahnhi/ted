<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\FtpConfigurationName;

class FtpConfiguration
{
    public function __construct(
        public readonly FtpConfigurationName $ftpConfigurationName,
        public readonly string $host,
        public readonly int $port,
        public readonly string $user,
        public readonly string $password,
        public readonly bool $hasPrivateKey,
        public readonly string|null $directory,
        public readonly string|null $trackingPath,
        public readonly string|null $deliveredPath,
    ) {
    }
}
