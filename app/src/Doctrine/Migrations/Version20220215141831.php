<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215141831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_order_item_logistic (id INT AUTO_INCREMENT NOT NULL, order_item_id INT DEFAULT NULL, batch_number VARCHAR(255) DEFAULT NULL, productVariantLogistic_id INT DEFAULT NULL, INDEX IDX_8C90A1DCE415FB15 (order_item_id), INDEX IDX_8C90A1DCBDB1D638 (productVariantLogistic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_order_item_logistic ADD CONSTRAINT FK_8C90A1DCE415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id)');
        $this->addSql('ALTER TABLE sylius_order_item_logistic ADD CONSTRAINT FK_8C90A1DCBDB1D638 FOREIGN KEY (productVariantLogistic_id) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sylius_order_item_logistic');
    }
}
