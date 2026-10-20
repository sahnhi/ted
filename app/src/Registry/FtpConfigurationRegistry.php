<?php

declare(strict_types=1);

namespace App\Registry;

use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Model\FtpConfiguration;

class FtpConfigurationRegistry implements FtpConfigurationRegistryInterface
{
    public function __construct(
        private iterable $ftpConfigurations = []
    ) {
    }

    final public function addFtpConfiguration(string $name, array $ftpConfiguration): void
    {
        $this->ftpConfigurations[$name] = $this->createFtpConfigurationModel($name, $ftpConfiguration);
    }

    /**
     * @return array<FtpConfiguration>
     */
    public function getFtpConfigurations(): iterable
    {
        return $this->ftpConfigurations;
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function getFtpConfigurationByName(FtpConfigurationName $ftpName): FtpConfiguration
    {
        return $this->ftpConfigurations[$ftpName->value] ?? throw new FtpConfigurationNotFoundException(sprintf('No FTP configuration found for "%s"', $ftpName->value));
    }

    private function createFtpConfigurationModel(string $name, array $ftpConfiguration): FtpConfiguration
    {
        return new FtpConfiguration(
            FtpConfigurationName::from($name),
            $ftpConfiguration['host'],
            $ftpConfiguration['port'],
            $ftpConfiguration['user'],
            $ftpConfiguration['password'],
            $ftpConfiguration['has_private_key'],
            $ftpConfiguration['directory'],
            $ftpConfiguration['tracking_path'],
            $ftpConfiguration['delivered_path'],
        );
    }
}
