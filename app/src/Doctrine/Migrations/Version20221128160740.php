<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128160740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quality ADD created_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quality ADD CONSTRAINT FK_7CB20B10DE12AB56 FOREIGN KEY (created_by) REFERENCES sylius_admin_user (id)');
        $this->addSql('CREATE INDEX IDX_7CB20B10DE12AB56 ON quality (created_by)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quality DROP FOREIGN KEY FK_7CB20B10DE12AB56');
        $this->addSql('DROP INDEX IDX_7CB20B10DE12AB56 ON quality');
        $this->addSql('ALTER TABLE quality DROP created_by');
    }
}
