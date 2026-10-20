<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250219102632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on zip code and country code';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_5E17BAB7771530985373C966 ON sylius_zip_code (code, country)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_5E17BAB7771530985373C966 ON sylius_zip_code');
    }
}
