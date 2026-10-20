<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220620100750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quality ADD number VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CB20B1096901F54 ON quality (number)');
        $this->addSql('ALTER TABLE tediber.quality MODIFY COLUMN order_item_logistic_id int(11) NOT NULL');
        $this->addSql('ALTER TABLE tediber.quality MODIFY COLUMN reason_id int(11) NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_7CB20B1096901F54 ON quality');
        $this->addSql('ALTER TABLE quality DROP number');
        $this->addSql('ALTER TABLE tediber.quality MODIFY COLUMN order_item_logistic_id int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE tediber.quality MODIFY COLUMN reason_id int(11) DEFAULT NULL;');
    }
}
