<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503081139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_channable_file_log (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, last_transmission DATETIME DEFAULT NULL, transmission_counter INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_feed_file (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, main_image VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C3EDC655A80EF684 (product_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_feed_file_additional_images (id INT AUTO_INCREMENT NOT NULL, product_feed_file_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_B4C56DD5A47D4F72 (product_feed_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_feed_file_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, google_category VARCHAR(255) NOT NULL, product_type VARCHAR(255) NOT NULL, material VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, object_state VARCHAR(255) NOT NULL, promotion_identifier VARCHAR(255) NOT NULL, group_identifier VARCHAR(255) NOT NULL, sizing_system VARCHAR(255) NOT NULL, composition VARCHAR(255) NOT NULL, tags VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_444685F22C2AC5D3 (translatable_id), UNIQUE INDEX app_product_feed_file_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_feed_file_importer (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, validated TINYINT(1) NOT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_feed_file ADD CONSTRAINT FK_C3EDC655A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_product_feed_file_additional_images ADD CONSTRAINT FK_B4C56DD5A47D4F72 FOREIGN KEY (product_feed_file_id) REFERENCES app_product_feed_file (id)');
        $this->addSql('ALTER TABLE app_product_feed_file_translation ADD CONSTRAINT FK_444685F22C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_feed_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant ADD ean13 INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_channable_file_log');
        $this->addSql('DROP TABLE app_product_feed_file');
        $this->addSql('DROP TABLE app_product_feed_file_additional_images');
        $this->addSql('DROP TABLE app_product_feed_file_translation');
        $this->addSql('DROP TABLE product_feed_file_importer');
        $this->addSql('ALTER TABLE sylius_product_variant DROP ean13');
    }
}
