<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180724134140 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_customer_support (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, support_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5D92E1A19395C3F3 (customer_id), UNIQUE INDEX customer_support_idx (customer_id, support_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_customer_support ADD CONSTRAINT FK_5D92E1A19395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE sylius_customer CHANGE locale locale VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_customer_support');
        $this->addSql('ALTER TABLE sylius_customer CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
    }
}
