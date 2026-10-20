<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180723104234 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_friend (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, INDEX IDX_6FCF429D9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_osteopath (id INT AUTO_INCREMENT NOT NULL, payment_method_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_62A56445AA1164F (payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_promotion_order (order_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_BF9CF6FB8D9F6D38 (order_id), INDEX IDX_BF9CF6FB139DF194 (promotion_id), PRIMARY KEY(order_id, promotion_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_friend ADD CONSTRAINT FK_6FCF429D9395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE sylius_osteopath ADD CONSTRAINT FK_62A56445AA1164F FOREIGN KEY (payment_method_id) REFERENCES sylius_address (id)');
        $this->addSql('ALTER TABLE sylius_promotion_order ADD CONSTRAINT FK_BF9CF6FB8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_promotion_order ADD CONSTRAINT FK_BF9CF6FB139DF194 FOREIGN KEY (promotion_id) REFERENCES sylius_promotion (id)');
        $this->addSql('ALTER TABLE sylius_customer ADD osteopath_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD CONSTRAINT FK_7E82D5E6E24EDBEF FOREIGN KEY (osteopath_id) REFERENCES sylius_osteopath (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E82D5E6E24EDBEF ON sylius_customer (osteopath_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customer DROP FOREIGN KEY FK_7E82D5E6E24EDBEF');
        $this->addSql('DROP TABLE sylius_friend');
        $this->addSql('DROP TABLE sylius_osteopath');
        $this->addSql('DROP TABLE sylius_promotion_order');
        $this->addSql('DROP INDEX UNIQ_7E82D5E6E24EDBEF ON sylius_customer');
        $this->addSql('ALTER TABLE sylius_customer DROP osteopath_id');
    }
}
