<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210810085256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_order_return (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, sav_status_id INT DEFAULT NULL, warehouse_status_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, history LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_A0D7271B8D9F6D38 (order_id), INDEX IDX_A0D7271B68CB27C9 (sav_status_id), INDEX IDX_A0D7271B54624E02 (warehouse_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_order_return_status (id INT AUTO_INCREMENT NOT NULL, statusType INT NOT NULL, savStatus VARCHAR(255) DEFAULT NULL, warehouseStatus VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_order_return ADD CONSTRAINT FK_A0D7271B8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE sylius_order_return ADD CONSTRAINT FK_A0D7271B68CB27C9 FOREIGN KEY (sav_status_id) REFERENCES sylius_order_return_status (id)');
        $this->addSql('ALTER TABLE sylius_order_return ADD CONSTRAINT FK_A0D7271B54624E02 FOREIGN KEY (warehouse_status_id) REFERENCES sylius_order_return_status (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_order_return DROP FOREIGN KEY FK_A0D7271B68CB27C9');
        $this->addSql('ALTER TABLE sylius_order_return DROP FOREIGN KEY FK_A0D7271B54624E02');
        $this->addSql('DROP TABLE sylius_order_return');
        $this->addSql('DROP TABLE sylius_order_return_status');
    }
}
