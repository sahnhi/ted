<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241028135216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates campaigns tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_bloc_variants (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, variant_id INT DEFAULT NULL, satellite_page_url VARCHAR(255) DEFAULT NULL, INDEX IDX_5453EA10F639F774 (campaign_id), INDEX IDX_5453EA103B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaign (id INT AUTO_INCREMENT NOT NULL, campaign_name VARCHAR(255) NOT NULL, campaign_slug VARCHAR(255) DEFAULT NULL, campaign_note LONGTEXT DEFAULT NULL, uuid VARCHAR(36) DEFAULT NULL, enabled TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_17957270997AA080 (campaign_slug), UNIQUE INDEX UNIQ_17957270D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaign_bloc_add (id INT AUTO_INCREMENT NOT NULL, campaign_bloc_id INT NOT NULL, image_url VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, tag VARCHAR(255) DEFAULT NULL, button_text VARCHAR(255) DEFAULT NULL, button_url VARCHAR(255) DEFAULT NULL, position INT NOT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_9ECA20CDFD3875EC (campaign_bloc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaign_bloc_display (id INT AUTO_INCREMENT NOT NULL, bloc_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, position INT NOT NULL, seoBlock TINYINT(1) NOT NULL, INDEX IDX_F82758ED5582E9C0 (bloc_id), INDEX IDX_F82758EDF639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaign_bloc_variants (id INT AUTO_INCREMENT NOT NULL, variant_id INT DEFAULT NULL, bloc_id INT DEFAULT NULL, position INT NOT NULL, satellite_page_url VARCHAR(255) DEFAULT NULL, INDEX IDX_2208C95C3B69A9AF (variant_id), INDEX IDX_2208C95C5582E9C0 (bloc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaign_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, main_image_url VARCHAR(255) DEFAULT NULL, mobile_image_url VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, meta_keywords VARCHAR(255) DEFAULT NULL, indexable TINYINT(1) NOT NULL, followable TINYINT(1) NOT NULL, start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', enabled TINYINT(1) NOT NULL, redirect_url VARCHAR(255) DEFAULT NULL, redirect_http_status_code INT NOT NULL, seo_block_title VARCHAR(255) DEFAULT NULL, seo_block_description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_835B1C462C2AC5D3 (translatable_id), INDEX campaign_translations_url_idx (url), INDEX campaign_translations_dates_idx (start_date, end_date), INDEX campaign_translations_enabled_idx (enabled), UNIQUE INDEX app_campaign_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_campaigns_blocs (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, enabled TINYINT(1) NOT NULL, offline_code VARCHAR(255) NOT NULL, buttonText VARCHAR(255) DEFAULT NULL, buttonLink VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_bloc_variants ADD CONSTRAINT FK_5453EA10F639F774 FOREIGN KEY (campaign_id) REFERENCES app_campaign (id)');
        $this->addSql('ALTER TABLE app_bloc_variants ADD CONSTRAINT FK_5453EA103B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_campaign_bloc_add ADD CONSTRAINT FK_9ECA20CDFD3875EC FOREIGN KEY (campaign_bloc_id) REFERENCES app_campaigns_blocs (id)');
        $this->addSql('ALTER TABLE app_campaign_bloc_display ADD CONSTRAINT FK_F82758ED5582E9C0 FOREIGN KEY (bloc_id) REFERENCES app_campaigns_blocs (id)');
        $this->addSql('ALTER TABLE app_campaign_bloc_display ADD CONSTRAINT FK_F82758EDF639F774 FOREIGN KEY (campaign_id) REFERENCES app_campaign (id)');
        $this->addSql('ALTER TABLE app_campaign_bloc_variants ADD CONSTRAINT FK_2208C95C3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_campaign_bloc_variants ADD CONSTRAINT FK_2208C95C5582E9C0 FOREIGN KEY (bloc_id) REFERENCES app_campaigns_blocs (id)');
        $this->addSql('ALTER TABLE app_campaign_translations ADD CONSTRAINT FK_835B1C462C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_campaign (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_bloc_variants DROP FOREIGN KEY FK_5453EA10F639F774');
        $this->addSql('ALTER TABLE app_campaign_bloc_display DROP FOREIGN KEY FK_F82758EDF639F774');
        $this->addSql('ALTER TABLE app_campaign_translations DROP FOREIGN KEY FK_835B1C462C2AC5D3');
        $this->addSql('ALTER TABLE app_campaign_bloc_add DROP FOREIGN KEY FK_9ECA20CDFD3875EC');
        $this->addSql('ALTER TABLE app_campaign_bloc_display DROP FOREIGN KEY FK_F82758ED5582E9C0');
        $this->addSql('ALTER TABLE app_campaign_bloc_variants DROP FOREIGN KEY FK_2208C95C5582E9C0');
        $this->addSql('DROP TABLE app_bloc_variants');
        $this->addSql('DROP TABLE app_campaign');
        $this->addSql('DROP TABLE app_campaign_bloc_add');
        $this->addSql('DROP TABLE app_campaign_bloc_display');
        $this->addSql('DROP TABLE app_campaign_bloc_variants');
        $this->addSql('DROP TABLE app_campaign_translations');
        $this->addSql('DROP TABLE app_campaigns_blocs');
    }
}
