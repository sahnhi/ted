<?php

declare(strict_types=1);

namespace App\Doctrine\Trait;

use App\Enum\FtpConfigurationName;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait FtpConfigurationAwareTrait
{
    #[ORM\Column(name: 'ftp_configuration_name', type: Types::STRING, nullable: true, enumType: FtpConfigurationName::class)]
    private FtpConfigurationName|null $ftpConfigurationName = null;

    public function getFtpConfigurationName(): FtpConfigurationName|null
    {
        return $this->ftpConfigurationName;
    }

    public function setFtpConfigurationName(FtpConfigurationName|null $ftpConfigurationName): void
    {
        $this->ftpConfigurationName = $ftpConfigurationName;
    }
}
