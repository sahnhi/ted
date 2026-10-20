<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425104530 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_store (id INT AUTO_INCREMENT NOT NULL, channel_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, INDEX IDX_6BE86A1672F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_order_store (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, seller_id INT DEFAULT NULL, store_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_DB8B55738D9F6D38 (order_id), INDEX IDX_DB8B55738DE820D9 (seller_id), INDEX IDX_DB8B5573B092A811 (store_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_seller (id INT AUTO_INCREMENT NOT NULL, store_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C13B3D00B092A811 (store_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_store ADD CONSTRAINT FK_6BE86A1672F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_order_store ADD CONSTRAINT FK_DB8B55738D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE sylius_order_store ADD CONSTRAINT FK_DB8B55738DE820D9 FOREIGN KEY (seller_id) REFERENCES sylius_seller (id)');
        $this->addSql('ALTER TABLE sylius_order_store ADD CONSTRAINT FK_DB8B5573B092A811 FOREIGN KEY (store_id) REFERENCES sylius_store (id)');
        $this->addSql('ALTER TABLE sylius_seller ADD CONSTRAINT FK_C13B3D00B092A811 FOREIGN KEY (store_id) REFERENCES sylius_store (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_order_store DROP FOREIGN KEY FK_DB8B5573B092A811');
        $this->addSql('ALTER TABLE sylius_seller DROP FOREIGN KEY FK_C13B3D00B092A811');
        $this->addSql('ALTER TABLE sylius_order_store DROP FOREIGN KEY FK_DB8B55738DE820D9');
        $this->addSql('DROP TABLE sylius_store');
        $this->addSql('DROP TABLE sylius_order_store');
        $this->addSql('DROP TABLE sylius_seller');
    }
}
