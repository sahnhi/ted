<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725092130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quality_image (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, INDEX IDX_D193CC6C7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quality_image ADD CONSTRAINT FK_D193CC6C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES quality (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quality DROP image_link, CHANGE order_item_logistic_id order_item_logistic_id INT DEFAULT NULL, CHANGE reason_id reason_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE quality_image');
        $this->addSql('ALTER TABLE quality ADD image_link VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, CHANGE order_item_logistic_id order_item_logistic_id INT NOT NULL, CHANGE reason_id reason_id INT NOT NULL');
    }
}
