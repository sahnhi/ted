<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190618153643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE erp_warehouse (id INT AUTO_INCREMENT NOT NULL, ftp_details_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_685E20C048756C08 (ftp_details_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE erp_mail_transmission (id INT AUTO_INCREMENT NOT NULL, mail VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_mailtransmissions_shippingmethods (erp_mail_transmission_id INT NOT NULL, shipping_method_id INT NOT NULL, INDEX IDX_F5FD14DE55A9747A (erp_mail_transmission_id), INDEX IDX_F5FD14DE5F7D6850 (shipping_method_id), PRIMARY KEY(erp_mail_transmission_id, shipping_method_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_mailtransmissions_warehouses (erp_mail_transmission_id INT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_BCD72A0655A9747A (erp_mail_transmission_id), INDEX IDX_BCD72A065080ECDE (warehouse_id), PRIMARY KEY(erp_mail_transmission_id, warehouse_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE erp_transmission (id INT AUTO_INCREMENT NOT NULL, shipping_method_id INT DEFAULT NULL, warehouse_id INT DEFAULT NULL, order_id INT DEFAULT NULL, transmission_state VARCHAR(255) NOT NULL, root_folder VARCHAR(255) NULL, created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL, INDEX IDX_C1BEFE085F7D6850 (shipping_method_id), INDEX IDX_C1BEFE085080ECDE (warehouse_id), INDEX IDX_C1BEFE088D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE erp_ftp_details (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, directory VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE erp_warehouse ADD CONSTRAINT FK_685E20C048756C08 FOREIGN KEY (ftp_details_id) REFERENCES erp_ftp_details (id)');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_shippingmethods ADD CONSTRAINT FK_F5FD14DE55A9747A FOREIGN KEY (erp_mail_transmission_id) REFERENCES erp_mail_transmission (id)');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_shippingmethods ADD CONSTRAINT FK_F5FD14DE5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_warehouses ADD CONSTRAINT FK_BCD72A0655A9747A FOREIGN KEY (erp_mail_transmission_id) REFERENCES erp_mail_transmission (id)');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_warehouses ADD CONSTRAINT FK_BCD72A065080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('ALTER TABLE erp_transmission ADD CONSTRAINT FK_C1BEFE085F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE erp_transmission ADD CONSTRAINT FK_C1BEFE085080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('ALTER TABLE erp_transmission ADD CONSTRAINT FK_C1BEFE088D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('INSERT INTO erp_ftp_details (id, name, host, user, password, directory) VALUES(1, "Spidy", "ftp.cluster011.ovh.net", "custommyvx", "3dfdsizeMezf5765f", "test-transmission/Spidy")');
        $this->addSql('INSERT INTO erp_ftp_details (id, name, host, user, password, directory) VALUES(2, "Vir", "ftp.cluster011.ovh.net", "custommyvx", "3dfdsizeMezf5765f", "test-transmission/Vir")');
        $this->addSql('INSERT INTO erp_warehouse (id,name, ftp_details_id) VALUES (1,"Spidy", 1)');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD ftp_details_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE1148756C08 FOREIGN KEY (ftp_details_id) REFERENCES erp_ftp_details (id)');
        $this->addSql('CREATE INDEX IDX_5FB0EE1148756C08 ON sylius_shipping_method (ftp_details_id)');
        $this->addSql('UPDATE sylius_shipping_method SET ftp_details_id = 2 WHERE code = "VIR" ');

        $this->addSql('ALTER TABLE sylius_shipping_method ADD warehouse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE115080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('CREATE INDEX IDX_5FB0EE115080ECDE ON sylius_shipping_method (warehouse_id)');
        $this->addSql('UPDATE `sylius_shipping_method` SET `warehouse_id` = \'1\' WHERE `sylius_shipping_method`.`id` = 6');
        $this->addSql('UPDATE `sylius_shipping_method` SET `warehouse_id` = \'1\' WHERE `sylius_shipping_method`.`id` = 12');
        $this->addSql('UPDATE `sylius_shipping_method` SET `warehouse_id` = \'1\' WHERE `sylius_shipping_method`.`id` = 13');
        $this->addSql('UPDATE `sylius_shipping_method` SET `warehouse_id` = \'1\' WHERE `sylius_shipping_method`.`id` = 15');
        $this->addSql('UPDATE `sylius_shipping_method` SET `warehouse_id` = \'1\' WHERE `sylius_shipping_method`.`id` = 16');

        $this->addSql('ALTER TABLE erp_transmission ADD root_folder_shipping_method VARCHAR(255) NULL, CHANGE root_folder root_folder_warehouse VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_warehouses DROP FOREIGN KEY FK_BCD72A065080ECDE');
        $this->addSql('ALTER TABLE erp_transmission DROP FOREIGN KEY FK_C1BEFE085080ECDE');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_shippingmethods DROP FOREIGN KEY FK_F5FD14DE55A9747A');
        $this->addSql('ALTER TABLE sylius_mailtransmissions_warehouses DROP FOREIGN KEY FK_BCD72A0655A9747A');
        $this->addSql('ALTER TABLE erp_warehouse DROP FOREIGN KEY FK_685E20C048756C08');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE1148756C08');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE115080ECDE');
        $this->addSql('DROP INDEX IDX_5FB0EE1148756C08 ON sylius_shipping_method');
        $this->addSql('DROP INDEX IDX_5FB0EE115080ECDE ON sylius_shipping_method');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP ftp_details_id');

        $this->addSql('ALTER TABLE sylius_shipping_method DROP warehouse_id');

        $this->addSql('DROP TABLE erp_warehouse');
        $this->addSql('DROP TABLE erp_mail_transmission');
        $this->addSql('DROP TABLE sylius_mailtransmissions_shippingmethods');
        $this->addSql('DROP TABLE sylius_mailtransmissions_warehouses');
        $this->addSql('DROP TABLE erp_transmission');
        $this->addSql('DROP TABLE erp_ftp_details');
    }
}
