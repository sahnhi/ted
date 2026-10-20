<?php

declare(strict_types=1);

namespace App\Tests\Unit\Registry;

use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Registry\FtpConfigurationRegistry;
use PHPUnit\Framework\TestCase;

class FtpConfigurationRegistryTest extends TestCase
{
    private readonly FtpConfigurationRegistry $ftpConfigurationRegistry;

    protected function setUp(): void
    {
        $this->ftpConfigurationRegistry = new FtpConfigurationRegistry();
    }

    /**
     * @dataProvider provideConfigurations
     */
    public function testItCorrectlyCreatesAFtpConfiguration(string $name, array $configuration): void
    {
        $this->ftpConfigurationRegistry->addFtpConfiguration($name, $configuration);

        self::assertCount(1, $this->ftpConfigurationRegistry->getFtpConfigurations());

        $ftpConfiguration = $this->ftpConfigurationRegistry->getFtpConfigurationByName(FtpConfigurationName::from($name));

        self::assertSame($configuration['host'], $ftpConfiguration->host);
        self::assertSame($configuration['port'], $ftpConfiguration->port);
        self::assertSame($configuration['user'], $ftpConfiguration->user);
        self::assertSame($configuration['password'], $ftpConfiguration->password);
        self::assertSame($configuration['has_private_key'], $ftpConfiguration->hasPrivateKey);
        self::assertSame($configuration['directory'], $ftpConfiguration->directory);
        self::assertSame($configuration['tracking_path'], $ftpConfiguration->trackingPath);
        self::assertSame($configuration['delivered_path'], $ftpConfiguration->deliveredPath);
    }

    /**
     * @dataProvider provideConfigurations
     */
    public function testItThrowsAnFtpConfigurationNotFoundExceptionIfTheConfigurationIsNotFound(string $name, array $configuration): void
    {
        $this->ftpConfigurationRegistry->addFtpConfiguration($name, $configuration);

        $this->expectException(FtpConfigurationNotFoundException::class);
        $this->expectExceptionMessage('No FTP configuration found for "Vir"');

        $this->ftpConfigurationRegistry->getFtpConfigurationByName(FtpConfigurationName::VIR);
    }

    private function provideConfigurations(): iterable
    {
        yield ['Spidy', [
            'host' => 'example_spidy_host',
            'port' => 2222,
            'user' => 'example_spidy_user',
            'password' => 'example_spidy_password',
            'has_private_key' => false,
            'directory' => '/foo/bar/directory',
            'tracking_path' => '/foo/bar/tracking',
            'delivered_path' => '/foo/bar/delivered',
        ]];
    }
}
