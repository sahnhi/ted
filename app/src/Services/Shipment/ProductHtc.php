<?php

namespace App\Services\Shipment;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class ProductHtc
{
    public const HTC_PRODUCT_YAML = '/config/data/product_variant_htc.yml';

    public function __construct(
        private readonly string $projectDir
    ) {
    }

    public function getProductVariantHtc(string $code): ?string
    {
        $this->ensureFileExist();

        $values = Yaml::parseFile($this->projectDir . self::HTC_PRODUCT_YAML);

        return isset($values[$code]) ? (string) $values[$code] : null;
    }

    private function ensureFileExist(): void
    {
        if (!file_exists($this->projectDir . self::HTC_PRODUCT_YAML)) {
            throw new FileNotFoundException('The product htc file does not exists');
        }
    }
}
