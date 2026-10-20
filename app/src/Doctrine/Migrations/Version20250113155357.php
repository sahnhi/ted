<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113155357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Exports the accounting data for analytics';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_export_accounting_data (id INT AUTO_INCREMENT NOT NULL, filepath VARCHAR(255) NOT NULL, startDate DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', endDate DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_export_accounting_data ADD scope VARCHAR(255) NOT NULL, CHANGE filepath filepath VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_export_accounting_data ADD updatedAt DATETIME NOT NULL, CHANGE createdAt createdAt DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_export_accounting_data DROP updatedAt, CHANGE createdAt createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE app_export_accounting_data DROP scope, CHANGE filepath filepath VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('DROP TABLE app_export_accounting_data');
    }
}
