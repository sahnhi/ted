<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181010095030 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_friend ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_friend ADD CONSTRAINT FK_6FCF429D9395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('CREATE INDEX IDX_6FCF429D9395C3F3 ON sylius_friend (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_friend DROP FOREIGN KEY FK_6FCF429D9395C3F3');
        $this->addSql('DROP INDEX IDX_6FCF429D9395C3F3 ON sylius_friend');
        $this->addSql('ALTER TABLE sylius_friend DROP customer_id');
    }
}
