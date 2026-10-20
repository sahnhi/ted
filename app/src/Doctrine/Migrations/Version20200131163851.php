<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200131163851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_shipping_stock (id INT AUTO_INCREMENT NOT NULL, warehouse INT DEFAULT NULL, product_variant INT DEFAULT NULL, shipping_date DATETIME DEFAULT NULL, INDEX IDX_79932121ECB38BFC (warehouse), INDEX IDX_79932121209AA41D (product_variant), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_shipping_stock ADD CONSTRAINT FK_79932121ECB38BFC FOREIGN KEY (warehouse) REFERENCES erp_warehouse (id)');
        $this->addSql('ALTER TABLE sylius_shipping_stock ADD CONSTRAINT FK_79932121209AA41D FOREIGN KEY (product_variant) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_shipping_stock');
    }
}
