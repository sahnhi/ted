<?php

declare(strict_types=1);

use App\Api\Odoo\Exception\OdooApiResponseException;
use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\IsFinal;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__ . '/../../src/ERP');

    $rules = [];

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\ERP\UI'))
        ->should(new NotHaveDependencyOutsideNamespace('App\ERP\Application', [
            'App\ERP\Infrastructure\Shared',
            App\Services\Sentry\Reporting::class,
            'Sentry',
            'Symfony\Component\Console',
        ]))
        ->andShould(new IsFinal())
        ->because('we want to protect our domain')
    ;

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\ERP\Application'))
        ->should(new NotHaveDependencyOutsideNamespace('App\ERP\Domain', [
            'App\Api\Odoo\Model',
            'App\Doctrine\Entity',
            'App\ERP\Application',
            'App\ERP\Infrastructure\Shared',
            'App\Services',
            'Doctrine',
            'Sylius',
            DateTime::class,
            OdooApiResponseException::class,
        ]))
        ->because('we want to protect our domain');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\ERP\Domain'))
        ->should(new NotHaveDependencyOutsideNamespace('App\ERP\Domain', [
            'App\Doctrine', DomainException::class, Throwable::class,
        ]))
        ->because('domain do not know about the implementation.');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\ERP\Infrastructure'))
        ->should(new NotHaveDependencyOutsideNamespace('App\ERP\Infrastructure', [
            'App\Api\Odoo\Model',
            'App\Doctrine',
            'App\ERP\Domain',
            'App\ERP\Application',
            'SM\Factory',
            'Symfony',
        ]))
        ->because('we need to know what to implement and deal with vendors.')
    ;

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\ERP\Infrastructure\Bridge'))
        ->should(new IsFinal())
        ->because('we need to know what to implement and deal with vendors.')
    ;

    $config
        ->add($classSet, ...$rules);
};
