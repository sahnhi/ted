<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127074501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_shipping_method_group (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, max_per_day INT DEFAULT NULL, max_per_slot INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD shipping_method_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE11C190515C FOREIGN KEY (shipping_method_group_id) REFERENCES app_shipping_method_group (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5FB0EE11C190515C ON sylius_shipping_method (shipping_method_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE11C190515C');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP shipping_method_group_id');
        $this->addSql('DROP TABLE app_shipping_method_group');
        $this->addSql('DROP INDEX IDX_5FB0EE11C190515C ON sylius_shipping_method');
    }
}
