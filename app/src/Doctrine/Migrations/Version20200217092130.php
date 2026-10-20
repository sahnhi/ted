<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200217092130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE erp_warehouse ADD opening_time TIME DEFAULT NULL, ADD closing_time TIME DEFAULT NULL, ADD cut_off TIME DEFAULT NULL, ADD opening_calendar_days LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD opening_time TIME DEFAULT NULL, ADD closing_time TIME DEFAULT NULL, ADD opening_calendar_days LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE erp_warehouse DROP opening_time, DROP closing_time, DROP cut_off, DROP opening_calendar_days');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP opening_time, DROP closing_time, DROP opening_calendar_days');
    }
}
