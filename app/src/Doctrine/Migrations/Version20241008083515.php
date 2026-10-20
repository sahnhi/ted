<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008083515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Setono Catalog Promotion plugin tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE setono_job_status__job (id INT AUTO_INCREMENT NOT NULL, version INT DEFAULT 1 NOT NULL, pids LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, exclusive TINYINT(1) NOT NULL, state VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updated_at DATETIME NOT NULL, startedAt DATETIME DEFAULT NULL, failedAt DATETIME DEFAULT NULL, finishedAt DATETIME DEFAULT NULL, timesOutAt DATETIME DEFAULT NULL, ttl INT NOT NULL, step INT NOT NULL, steps INT DEFAULT NULL, metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', error LONGTEXT DEFAULT NULL, INDEX IDX_A79E91E43625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_catalog_promotion__promotion (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, priority INT NOT NULL, exclusive TINYINT(1) NOT NULL, manuallyDiscountedProductsExcluded TINYINT(1) NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, enabled TINYINT(1) NOT NULL, discount NUMERIC(10, 5) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_38E07DF377153098 (code), INDEX IDX_38E07DF38B8E8428 (created_at), INDEX IDX_38E07DF343625D9F (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_catalog_promotion__promotion_channels (promotion_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_AE73FF1F139DF194 (promotion_id), INDEX IDX_AE73FF1F72F5A1AA (channel_id), PRIMARY KEY(promotion_id, channel_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_catalog_promotion__promotion_rule (id INT AUTO_INCREMENT NOT NULL, promotion_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_3341E240139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE setono_sylius_catalog_promotion__promotion_channels ADD CONSTRAINT FK_AE73FF1F139DF194 FOREIGN KEY (promotion_id) REFERENCES setono_sylius_catalog_promotion__promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_catalog_promotion__promotion_channels ADD CONSTRAINT FK_AE73FF1F72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_catalog_promotion__promotion_rule ADD CONSTRAINT FK_3341E240139DF194 FOREIGN KEY (promotion_id) REFERENCES setono_sylius_catalog_promotion__promotion (id)');

        $this->addSql('ALTER TABLE sylius_channel_pricing ADD manuallyDiscounted TINYINT(1) DEFAULT \'0\' NOT NULL, ADD multiplier NUMERIC(8, 4) DEFAULT \'1\' NOT NULL, ADD bulkIdentifier VARCHAR(255) DEFAULT NULL, ADD appliedPromotions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD createdAt DATETIME DEFAULT NULL, ADD updatedAt DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_7801820C86CA693C ON sylius_channel_pricing (createdAt)');
        $this->addSql('CREATE INDEX IDX_7801820CBD425E0E ON sylius_channel_pricing (updatedAt)');
        $this->addSql('CREATE INDEX IDX_7801820CA49AA95B ON sylius_channel_pricing (multiplier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE setono_sylius_catalog_promotion__promotion_channels DROP FOREIGN KEY FK_AE73FF1F139DF194');
        $this->addSql('ALTER TABLE setono_sylius_catalog_promotion__promotion_rule DROP FOREIGN KEY FK_3341E240139DF194');
        $this->addSql('DROP TABLE setono_job_status__job');
        $this->addSql('DROP TABLE setono_sylius_catalog_promotion__promotion');
        $this->addSql('DROP TABLE setono_sylius_catalog_promotion__promotion_channels');
        $this->addSql('DROP TABLE setono_sylius_catalog_promotion__promotion_rule');
        $this->addSql('DROP INDEX IDX_7801820C86CA693C ON sylius_channel_pricing');
        $this->addSql('DROP INDEX IDX_7801820CBD425E0E ON sylius_channel_pricing');
        $this->addSql('DROP INDEX IDX_7801820CA49AA95B ON sylius_channel_pricing');
        $this->addSql('ALTER TABLE sylius_channel_pricing DROP manuallyDiscounted, DROP multiplier, DROP bulkIdentifier, DROP appliedPromotions, DROP createdAt, DROP updatedAt');
    }
}
