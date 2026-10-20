<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220511143934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quality (id INT AUTO_INCREMENT NOT NULL, order_item_logistic_id INT DEFAULT NULL, reason_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, validation_required TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, sav_comment LONGTEXT NOT NULL, quality_comment LONGTEXT DEFAULT NULL, image_link VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_7CB20B10DFF25AB (order_item_logistic_id), INDEX IDX_7CB20B1059BB1592 (reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quality ADD CONSTRAINT FK_7CB20B10DFF25AB FOREIGN KEY (order_item_logistic_id) REFERENCES sylius_order_item_logistic (id)');
        $this->addSql('ALTER TABLE quality ADD CONSTRAINT FK_7CB20B1059BB1592 FOREIGN KEY (reason_id) REFERENCES sylius_reason (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE quality');
    }
}
