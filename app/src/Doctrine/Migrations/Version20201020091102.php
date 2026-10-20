<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201020091102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_shipping_method ADD has_tracking TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD floor TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD tuned TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_shipping_method DROP has_tracking');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP floor');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP tuned');
    }
}
