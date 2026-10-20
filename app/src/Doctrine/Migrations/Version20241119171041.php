<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241119171041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_satellite_page (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT NOT NULL, enabled TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_3CDE88A4A80EF684 (product_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_rtb_infos (satellite_page_id INT NOT NULL, rtb_infos_id INT NOT NULL, INDEX IDX_95A9F0E08B4470BF (satellite_page_id), INDEX IDX_95A9F0E0B4CE313C (rtb_infos_id), PRIMARY KEY(satellite_page_id, rtb_infos_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_claim (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, satellitePage_id INT DEFAULT NULL, INDEX IDX_C5B0484E3078346A (satellitePage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_claim_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, claim VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_23ABA6EB2C2AC5D3 (translatable_id), UNIQUE INDEX app_satellite_page_claim_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_technical_details (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, technicalInfo_id INT DEFAULT NULL, INDEX IDX_4BA2CE14213543B3 (technicalInfo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_technical_details_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_E77F61522C2AC5D3 (translatable_id), UNIQUE INDEX app_satellite_page_technical_details_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_technical_info (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, satellitePage_id INT DEFAULT NULL, INDEX IDX_85C41D5A3078346A (satellitePage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_technical_info_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_46C27EB2C2AC5D3 (translatable_id), UNIQUE INDEX app_satellite_page_technical_info_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_satellite_page_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, tag VARCHAR(255) DEFAULT NULL, main_phrase VARCHAR(255) DEFAULT NULL, focus LONGTEXT DEFAULT NULL, satellite_url VARCHAR(255) NOT NULL, quality_claim VARCHAR(255) DEFAULT NULL, technical_claim VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_D5DB20102C2AC5D3 (translatable_id), UNIQUE INDEX app_satellite_page_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_satellite_page ADD CONSTRAINT FK_3CDE88A4A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_satellite_page_rtb_infos ADD CONSTRAINT FK_95A9F0E08B4470BF FOREIGN KEY (satellite_page_id) REFERENCES app_satellite_page (id)');
        $this->addSql('ALTER TABLE app_satellite_page_rtb_infos ADD CONSTRAINT FK_95A9F0E0B4CE313C FOREIGN KEY (rtb_infos_id) REFERENCES app_rtb_infos (id)');
        $this->addSql('ALTER TABLE app_satellite_page_claim ADD CONSTRAINT FK_C5B0484E3078346A FOREIGN KEY (satellitePage_id) REFERENCES app_satellite_page (id)');
        $this->addSql('ALTER TABLE app_satellite_page_claim_translation ADD CONSTRAINT FK_23ABA6EB2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_satellite_page_claim (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_satellite_page_technical_details ADD CONSTRAINT FK_4BA2CE14213543B3 FOREIGN KEY (technicalInfo_id) REFERENCES app_satellite_page_technical_info (id)');
        $this->addSql('ALTER TABLE app_satellite_page_technical_details_translation ADD CONSTRAINT FK_E77F61522C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_satellite_page_technical_details (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_satellite_page_technical_info ADD CONSTRAINT FK_85C41D5A3078346A FOREIGN KEY (satellitePage_id) REFERENCES app_satellite_page (id)');
        $this->addSql('ALTER TABLE app_satellite_page_technical_info_translation ADD CONSTRAINT FK_46C27EB2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_satellite_page_technical_info (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_satellite_page_translation ADD CONSTRAINT FK_D5DB20102C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_satellite_page (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5DB2010B88D623A ON app_satellite_page_translation (satellite_url)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_satellite_page_rtb_infos DROP FOREIGN KEY FK_95A9F0E08B4470BF');
        $this->addSql('ALTER TABLE app_satellite_page_claim DROP FOREIGN KEY FK_C5B0484E3078346A');
        $this->addSql('ALTER TABLE app_satellite_page_technical_info DROP FOREIGN KEY FK_85C41D5A3078346A');
        $this->addSql('ALTER TABLE app_satellite_page_translation DROP FOREIGN KEY FK_D5DB20102C2AC5D3');
        $this->addSql('ALTER TABLE app_satellite_page_claim_translation DROP FOREIGN KEY FK_23ABA6EB2C2AC5D3');
        $this->addSql('ALTER TABLE app_satellite_page_technical_details_translation DROP FOREIGN KEY FK_E77F61522C2AC5D3');
        $this->addSql('ALTER TABLE app_satellite_page_technical_details DROP FOREIGN KEY FK_4BA2CE14213543B3');
        $this->addSql('ALTER TABLE app_satellite_page_technical_info_translation DROP FOREIGN KEY FK_46C27EB2C2AC5D3');
        $this->addSql('DROP TABLE app_satellite_page');
        $this->addSql('DROP TABLE app_satellite_page_rtb_infos');
        $this->addSql('DROP TABLE app_satellite_page_claim');
        $this->addSql('DROP TABLE app_satellite_page_claim_translation');
        $this->addSql('DROP TABLE app_satellite_page_technical_details');
        $this->addSql('DROP TABLE app_satellite_page_technical_details_translation');
        $this->addSql('DROP TABLE app_satellite_page_technical_info');
        $this->addSql('DROP TABLE app_satellite_page_technical_info_translation');
        $this->addSql('DROP TABLE app_satellite_page_translation');
    }
}
