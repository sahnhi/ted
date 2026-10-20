<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220707121943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_product_google_rss (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, sku VARCHAR(255) DEFAULT NULL, gtin VARCHAR(255) DEFAULT NULL, mpn VARCHAR(255) DEFAULT NULL, url_review VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C026F3404584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_google_rss ADD CONSTRAINT FK_C026F3404584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_product_google_rss');
    }
}
