<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830161944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE traduit traduit INT DEFAULT 0, CHANGE langue langue VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_bi_command CHANGE total_ht total_ht DOUBLE PRECISION DEFAULT NULL, CHANGE taxes taxes DOUBLE PRECISION DEFAULT NULL, CHANGE total_ttc total_ttc DOUBLE PRECISION DEFAULT NULL, CHANGE familiarite familiarite VARCHAR(255) DEFAULT NULL, CHANGE creneau creneau DATETIME DEFAULT NULL, CHANGE transporteur transporteur VARCHAR(255) DEFAULT NULL, CHANGE methode_paiement methode_paiement VARCHAR(255) DEFAULT NULL, CHANGE paiement paiement VARCHAR(255) DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(255) DEFAULT NULL, CHANGE departement departement VARCHAR(255) DEFAULT NULL, CHANGE coupon coupon VARCHAR(255) DEFAULT NULL, CHANGE cross_sell cross_sell INT DEFAULT 0, CHANGE repeated repeated INT DEFAULT NULL, CHANGE first_command first_command VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_bi_product CHANGE total_ht total_ht DOUBLE PRECISION DEFAULT NULL, CHANGE taxes taxes DOUBLE PRECISION DEFAULT NULL, CHANGE total_ttc total_ttc DOUBLE PRECISION DEFAULT NULL, CHANGE methode_paiement methode_paiement VARCHAR(255) DEFAULT NULL, CHANGE paiement paiement VARCHAR(255) DEFAULT NULL, CHANGE creneau creneau DATETIME DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(255) DEFAULT NULL, CHANGE departement departement VARCHAR(255) DEFAULT NULL, CHANGE coupon coupon VARCHAR(255) DEFAULT NULL, CHANGE variante variante VARCHAR(255) DEFAULT NULL, CHANGE type_produit type_produit VARCHAR(255) DEFAULT NULL, CHANGE familiarite familiarite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_bi_product CHANGE numero_commande numero_commande VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE numero_commande numero_commande VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_command CHANGE numero_commande numero_commande VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE date_naissance date_naissance DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE date_naissance date_naissance DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_command CHANGE numero_commande numero_commande INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE numero_commande numero_commande INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_product CHANGE numero_commande numero_commande INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_bi_command CHANGE total_ht total_ht DOUBLE PRECISION NOT NULL, CHANGE taxes taxes DOUBLE PRECISION NOT NULL, CHANGE total_ttc total_ttc DOUBLE PRECISION NOT NULL, CHANGE familiarite familiarite VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE creneau creneau VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE transporteur transporteur VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE methode_paiement methode_paiement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE paiement paiement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE code_postal code_postal VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE departement departement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE coupon coupon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE cross_sell cross_sell INT NOT NULL, CHANGE `repeat` `repeat` INT NOT NULL, CHANGE first_command first_command VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE sylius_bi_logistic_review CHANGE traduit traduit INT NOT NULL, CHANGE langue langue VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE sylius_bi_product CHANGE total_ht total_ht DOUBLE PRECISION NOT NULL, CHANGE taxes taxes DOUBLE PRECISION NOT NULL, CHANGE total_ttc total_ttc DOUBLE PRECISION NOT NULL, CHANGE methode_paiement methode_paiement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE paiement paiement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE creneau creneau DATETIME NOT NULL, CHANGE code_postal code_postal VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE departement departement VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE coupon coupon VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE variante variante VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE type_produit type_produit VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE familiarite familiarite VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
