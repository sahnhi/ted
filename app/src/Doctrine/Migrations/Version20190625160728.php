<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190625160728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_reason_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_reason (id INT AUTO_INCREMENT NOT NULL, channel_id INT DEFAULT NULL, type INT NOT NULL, name VARCHAR(255) NOT NULL, placeholder VARCHAR(255) DEFAULT NULL, productRule INT DEFAULT NULL, groupName_id INT DEFAULT NULL, INDEX IDX_19966F072F5A1AA (channel_id), INDEX IDX_19966F0941F9A19 (groupName_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_order_reason (id INT AUTO_INCREMENT NOT NULL, reason_id INT DEFAULT NULL, order_id INT NOT NULL, variant_id INT DEFAULT NULL, created_by INT DEFAULT NULL, quantity INT DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, commercialOffer_id INT DEFAULT NULL, INDEX IDX_3CF1901859BB1592 (reason_id), INDEX IDX_3CF190188D9F6D38 (order_id), INDEX IDX_3CF19018BC69AC74 (commercialOffer_id), INDEX IDX_3CF190183B69A9AF (variant_id), INDEX IDX_3CF19018DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_reason ADD CONSTRAINT FK_19966F072F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_reason ADD CONSTRAINT FK_19966F0941F9A19 FOREIGN KEY (groupName_id) REFERENCES sylius_reason_group (id)');
        $this->addSql('ALTER TABLE sylius_order_reason ADD CONSTRAINT FK_3CF1901859BB1592 FOREIGN KEY (reason_id) REFERENCES sylius_reason (id)');
        $this->addSql('ALTER TABLE sylius_order_reason ADD CONSTRAINT FK_3CF190188D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order_reason ADD CONSTRAINT FK_3CF19018BC69AC74 FOREIGN KEY (commercialOffer_id) REFERENCES sylius_reason (id)');
        $this->addSql('ALTER TABLE sylius_order_reason ADD CONSTRAINT FK_3CF190183B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_order_reason ADD CONSTRAINT FK_3CF19018DE12AB56 FOREIGN KEY (created_by) REFERENCES sylius_admin_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_reason DROP FOREIGN KEY FK_19966F0941F9A19');
        $this->addSql('ALTER TABLE sylius_order_reason DROP FOREIGN KEY FK_3CF1901859BB1592');
        $this->addSql('ALTER TABLE sylius_order_reason DROP FOREIGN KEY FK_3CF19018BC69AC74');
        $this->addSql('DROP TABLE sylius_reason_group');
        $this->addSql('DROP TABLE sylius_reason');
        $this->addSql('DROP TABLE sylius_order_reason');
    }
}
