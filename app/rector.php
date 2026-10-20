<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/features',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withTypeCoverageLevel(10) // On 37 (09/24)
    ->withDeadCodeLevel(17) // On 40 (09/24)
    ->withPhpSets(php81: true)
    ->withSets([
        SymfonySetList::SYMFONY_44,
    ])
    ->withSkip([
        EventListenerToEventSubscriberRector::class,
        ChangeSwitchToMatchRector::class, // Creates type issues
        ReadOnlyPropertyRector::class => [
            __DIR__ . '/src/Api/TrustPilot.php', // Used because GuzzleMockHandler can't edit readonly properties
        ],
    ])
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/srcApp_KernelDevDebugContainer.xml')
    ->withCache(
        cacheDirectory: '/app/var/rector',
        cacheClass: FileCacheStorage::class,
    )
    ;
