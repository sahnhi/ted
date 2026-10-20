<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611051624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_warehouse ADD ftp_configuration_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD ftp_configuration_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_warehouse DROP ftp_configuration_name');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP ftp_configuration_name');
    }
}
