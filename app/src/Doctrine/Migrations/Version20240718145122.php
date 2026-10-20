<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718145122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_file_channable_row (id INT AUTO_INCREMENT NOT NULL, channable_file_log_id INT DEFAULT NULL, google_product_id VARCHAR(255) NOT NULL, sku VARCHAR(255) NOT NULL, gtin VARCHAR(255) NOT NULL, parent_sku VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, url_product VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, product_highlight VARCHAR(255) DEFAULT NULL, google_product_category VARCHAR(255) NOT NULL, product_type VARCHAR(255) NOT NULL, is_bundle TINYINT(1) NOT NULL, public VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, price_with_vat INT NOT NULL, promotion_price_with_vat INT DEFAULT NULL, currency VARCHAR(255) NOT NULL, shipping VARCHAR(255) NOT NULL, maximum_handling_time INT DEFAULT NULL, minimum_handling_time INT DEFAULT NULL, maximum_delivery_time INT DEFAULT NULL, minimum_delivery_time INT DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, size VARCHAR(255) NOT NULL, material VARCHAR(255) NOT NULL, thickness VARCHAR(255) NOT NULL, made_in VARCHAR(255) NOT NULL, expedition_country VARCHAR(255) DEFAULT NULL, product_details VARCHAR(255) DEFAULT NULL, product_length VARCHAR(255) DEFAULT NULL, product_width VARCHAR(255) DEFAULT NULL, product_height VARCHAR(255) DEFAULT NULL, product_weight VARCHAR(255) DEFAULT NULL, packging_details VARCHAR(255) DEFAULT NULL, packging_length VARCHAR(255) DEFAULT NULL, packging_width VARCHAR(255) DEFAULT NULL, packging_height VARCHAR(255) DEFAULT NULL, packging_weight VARCHAR(255) DEFAULT NULL, product_characteristics VARCHAR(255) DEFAULT NULL, certifications VARCHAR(255) DEFAULT NULL, images LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', promotion VARCHAR(255) DEFAULT NULL, mobile_url VARCHAR(255) DEFAULT NULL, INDEX IDX_F38A08DC24D8F64E (channable_file_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_file_channable_row ADD CONSTRAINT FK_F38A08DC24D8F64E FOREIGN KEY (channable_file_log_id) REFERENCES app_channable_file_log (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_file_channable_row');
    }
}
