<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614071301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_warehouse RENAME INDEX name TO UNIQ_685E20C05E237E06');
        $this->addSql('ALTER TABLE sylius_shipping_method CHANGE warehouse_id warehouse_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_warehouse RENAME INDEX uniq_685e20c05e237e06 TO name');
        $this->addSql('ALTER TABLE sylius_shipping_method CHANGE warehouse_id warehouse_id INT DEFAULT NULL');
    }
}
