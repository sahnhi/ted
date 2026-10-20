<?php

namespace App\Doctrine\Entity;

use App\Enum\FtpConfigurationName;

interface FtpConfigurationAwareInterface
{
    public function getFtpConfigurationName(): FtpConfigurationName|null;

    public function setFtpConfigurationName(FtpConfigurationName|null $ftpName): void;
}
