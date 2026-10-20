<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200429075238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_shipping_category_service_rules (shipping_category_id INT NOT NULL, service_rules_id INT NOT NULL, INDEX IDX_793144FC9E2D1A41 (shipping_category_id), INDEX IDX_793144FC21FC979 (service_rules_id), PRIMARY KEY(shipping_category_id, service_rules_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_shipping_category_service_rules ADD CONSTRAINT FK_793144FC9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('ALTER TABLE sylius_shipping_category_service_rules ADD CONSTRAINT FK_793144FC21FC979 FOREIGN KEY (service_rules_id) REFERENCES erp_service_rules (id)');
        $this->addSql('ALTER TABLE erp_service_rules DROP FOREIGN KEY FK_4E3D4BDB9E2D1A41');
        $this->addSql('DROP INDEX IDX_4E3D4BDB9E2D1A41 ON erp_service_rules');
        $this->addSql('ALTER TABLE erp_service_rules DROP shipping_category_id, DROP shipping_plan_file');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_shipping_category_service_rules');
        $this->addSql('ALTER TABLE erp_service_rules ADD shipping_category_id INT DEFAULT NULL, ADD shipping_plan_file VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE erp_service_rules ADD CONSTRAINT FK_4E3D4BDB9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('CREATE INDEX IDX_4E3D4BDB9E2D1A41 ON erp_service_rules (shipping_category_id)');
    }
}
