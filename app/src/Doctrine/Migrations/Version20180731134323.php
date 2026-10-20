<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180731134323 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_familiarity (id INT AUTO_INCREMENT NOT NULL, wording VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_customers_familiarities (customer_id INT NOT NULL, familiarity_id INT NOT NULL, INDEX IDX_E1CCBBB19395C3F3 (customer_id), INDEX IDX_E1CCBBB11B962257 (familiarity_id), PRIMARY KEY(customer_id, familiarity_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_customers_familiarities ADD CONSTRAINT FK_E1CCBBB19395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE sylius_customers_familiarities ADD CONSTRAINT FK_E1CCBBB11B962257 FOREIGN KEY (familiarity_id) REFERENCES sylius_familiarity (id)');
        $this->addSql('ALTER TABLE sylius_customer ADD referral VARCHAR(100) NOT NULL, ADD optin TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customers_familiarities DROP FOREIGN KEY FK_E1CCBBB11B962257');
        $this->addSql('DROP TABLE sylius_familiarity');
        $this->addSql('DROP TABLE sylius_customers_familiarities');
        $this->addSql('ALTER TABLE sylius_customer DROP referral, DROP optin');
    }
}
