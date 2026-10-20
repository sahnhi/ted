<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605095633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_customers_promotion_coupons (customer_id INT NOT NULL, promotion_coupon_id INT NOT NULL, INDEX IDX_249D8C839395C3F3 (customer_id), INDEX IDX_249D8C8317B24436 (promotion_coupon_id), PRIMARY KEY(customer_id, promotion_coupon_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_customers_promotion_coupons ADD CONSTRAINT FK_249D8C839395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id)');
        $this->addSql('ALTER TABLE sylius_customers_promotion_coupons ADD CONSTRAINT FK_249D8C8317B24436 FOREIGN KEY (promotion_coupon_id) REFERENCES sylius_promotion_coupon (id)');
        $this->addSql('ALTER TABLE sylius_customer ADD sponsor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_customer ADD CONSTRAINT FK_7E82D5E612F7FB51 FOREIGN KEY (sponsor_id) REFERENCES sylius_customer (id)');
        $this->addSql('CREATE INDEX IDX_7E82D5E612F7FB51 ON sylius_customer (sponsor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_customers_promotion_coupons');
        $this->addSql('ALTER TABLE sylius_customer DROP FOREIGN KEY FK_7E82D5E612F7FB51');
        $this->addSql('DROP INDEX IDX_7E82D5E612F7FB51 ON sylius_customer');
        $this->addSql('ALTER TABLE sylius_customer DROP sponsor_id');
    }
}
