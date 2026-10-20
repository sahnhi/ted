<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241014124519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create promotion category table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_promotion_category (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_A00976FD77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_promotion_category_promotions (id INT AUTO_INCREMENT NOT NULL, promotion_category_id INT NOT NULL, promotion_id INT NOT NULL, priority INT NOT NULL, INDEX IDX_36BE4E2FC3B85438 (promotion_category_id), INDEX IDX_36BE4E2F139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_promotion_category_promotions ADD CONSTRAINT FK_36BE4E2FC3B85438 FOREIGN KEY (promotion_category_id) REFERENCES app_promotion_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_promotion_category_promotions ADD CONSTRAINT FK_36BE4E2F139DF194 FOREIGN KEY (promotion_id) REFERENCES sylius_promotion (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_promotion_category_promotions DROP FOREIGN KEY FK_36BE4E2FC3B85438');
        $this->addSql('DROP TABLE app_promotion_category');
        $this->addSql('DROP TABLE app_promotion_category_promotions');
    }
}
