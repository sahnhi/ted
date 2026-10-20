<?php

declare(strict_types=1);

namespace App\Tests\Functional\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class FixtureLoader
{
    public const TABLE_TO_TRUNCATE = [
        'sylius_order_item',
    ];

    private $application;

    public function __construct(
        KernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager,
        private $dataFixturesPath
    ) {
        $this->application = new Application($kernel);
    }

    public function load(): void
    {
        $this->application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'hautelook:fixtures:load',
            '--env' => 'test',
        ]);

        $this->truncate();

        $this->application->run($input, new NullOutput());
    }

    private function truncate(): void
    {
        $files = glob($this->dataFixturesPath . DIRECTORY_SEPARATOR . '*.yml');
        $results = $this->getLoader()->loadFiles($files);

        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $c = $connection->beginTransaction();
        try {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
            foreach ($results->getObjects() as $entity) {
                $classMetaData = $this->entityManager->getClassMetadata($entity::class);
                $q = $dbPlatform->getTruncateTableSql($classMetaData->getTableName());
                $connection->executeStatement($q);
            }
            foreach (self::TABLE_TO_TRUNCATE as $tableName) {
                $q = $dbPlatform->getTruncateTableSql($tableName);
                $connection->executeStatement($q);
            }
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception) {
            $connection->rollback();
        }
    }

    private function getLoader(): NativeLoader
    {
        $fakerGenerator = Factory::create('fr_FR');
        $fakerGenerator->addProvider(new AliceProvider());

        return new NativeLoader($fakerGenerator);
    }
}
