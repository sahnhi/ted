<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180828122938 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_bi_logistic_review (id INT AUTO_INCREMENT NOT NULL, numero_commande INT NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, note_livraison INT NOT NULL, commentaire VARCHAR(255) NOT NULL, NPS INT NOT NULL, date_naissance DATETIME NOT NULL, date_creation DATETIME NOT NULL, traduit INT NOT NULL, status VARCHAR(255) NOT NULL, langue VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_bi_command (id INT AUTO_INCREMENT NOT NULL, numero_commande INT NOT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, etat_paiement VARCHAR(255) NOT NULL, statut_livraison VARCHAR(255) NOT NULL, total_ht DOUBLE PRECISION NOT NULL, taxes DOUBLE PRECISION NOT NULL, total_ttc DOUBLE PRECISION NOT NULL, familiarite VARCHAR(255) NOT NULL, creneau VARCHAR(255) NOT NULL, transporteur VARCHAR(255) NOT NULL, channel VARCHAR(255) NOT NULL, methode_paiement VARCHAR(255) NOT NULL, paiement VARCHAR(255) NOT NULL, email_facturation VARCHAR(255) NOT NULL, ville_livraison VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, departement VARCHAR(255) NOT NULL, pays_livraison VARCHAR(255) NOT NULL, coupon VARCHAR(255) NOT NULL, cross_sell INT NOT NULL, repeated INT NOT NULL, first_command VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_bi_product (id INT AUTO_INCREMENT NOT NULL, numero_commande INT NOT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, total_ht DOUBLE PRECISION NOT NULL, taxes DOUBLE PRECISION NOT NULL, total_ttc DOUBLE PRECISION NOT NULL, channel VARCHAR(255) NOT NULL, methode_paiement VARCHAR(255) NOT NULL, paiement VARCHAR(255) NOT NULL, email_facturation VARCHAR(255) NOT NULL, creneau VARCHAR(255) NOT NULL, transporteur VARCHAR(255) NOT NULL, ville_livraison VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, departement VARCHAR(255) NOT NULL, pays_livraison VARCHAR(255) NOT NULL, coupon VARCHAR(255) NOT NULL, variante VARCHAR(255) NOT NULL, quantite INT NOT NULL, type_produit VARCHAR(255) NOT NULL, familiarite VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_bi_logistic_review');
        $this->addSql('DROP TABLE sylius_bi_command');
        $this->addSql('DROP TABLE sylius_bi_product');
    }
}
