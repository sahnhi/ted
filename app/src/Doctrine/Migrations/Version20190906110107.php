<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190906110107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_bi_logistic_review');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_bi_logistic_review (id INT AUTO_INCREMENT NOT NULL, numero_commande VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, prenom VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ville VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, note_livraison INT NOT NULL, commentaire LONGTEXT NOT NULL COLLATE utf8_unicode_ci, NPS INT NOT NULL, date_naissance DATETIME DEFAULT NULL, date_creation DATETIME NOT NULL, traduit INT DEFAULT 0, status VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, langue VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }
}
