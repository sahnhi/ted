<?php

declare(strict_types=1);

use Arkitect\CLI\Config;

$erpConfig = require __DIR__ . '/config/test/erp.php';

return static function (Config $config) use ($erpConfig): void {
    $erpConfig($config);
};
