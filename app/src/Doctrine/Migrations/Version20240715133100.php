<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240715133100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete useless erp_ftp_details table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_warehouse DROP FOREIGN KEY FK_685E20C048756C08');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE1148756C08');
        $this->addSql('DROP INDEX IDX_685E20C048756C08 ON erp_warehouse');
        $this->addSql('ALTER TABLE erp_warehouse DROP COLUMN ftp_details_id');
        $this->addSql('DROP INDEX IDX_5FB0EE1148756C08 ON sylius_shipping_method');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP COLUMN ftp_details_id');
        $this->addSql('DROP TABLE erp_ftp_details');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE erp_ftp_details (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, directory VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD ftp_details_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_5FB0EE1148756C08 ON sylius_shipping_method (ftp_details_id)');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE1148756C08 FOREIGN KEY (ftp_details_id) REFERENCES erp_ftp_details (id)');
        $this->addSql('ALTER TABLE erp_warehouse ADD ftp_details_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_685E20C048756C08 ON erp_warehouse (ftp_details_id)');
        $this->addSql('ALTER TABLE erp_warehouse ADD CONSTRAINT FK_685E20C048756C08 FOREIGN KEY (ftp_details_id) REFERENCES erp_ftp_details (id)');
    }
}
