<?php

declare(strict_types=1);

namespace App\Registry;

use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Model\FtpConfiguration;

interface FtpConfigurationRegistryInterface
{
    /**
     * @return array<FtpConfiguration>
     */
    public function getFtpConfigurations(): iterable;

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function getFtpConfigurationByName(FtpConfigurationName $ftpName): FtpConfiguration;
}
