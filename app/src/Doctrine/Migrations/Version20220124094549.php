<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220124094549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipment ADD warehouse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B335080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('CREATE INDEX IDX_FD707B335080ECDE ON sylius_shipment (warehouse_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B335080ECDE');
        $this->addSql('DROP INDEX IDX_FD707B335080ECDE ON sylius_shipment');
        $this->addSql('ALTER TABLE sylius_shipment DROP warehouse_id');
    }
}
