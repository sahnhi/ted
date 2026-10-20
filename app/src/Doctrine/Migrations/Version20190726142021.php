<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190726142021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_product_combo (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT DEFAULT NULL, product_variant_logistic_id INT DEFAULT NULL, quantity DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_FCC77F38A80EF684 (product_variant_id), UNIQUE INDEX UNIQ_FCC77F3856576424 (product_variant_logistic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_order_item_product_combo (id INT AUTO_INCREMENT NOT NULL, order_item_id INT DEFAULT NULL, product_combo_id INT DEFAULT NULL, INDEX IDX_E1295163E415FB15 (order_item_id), INDEX IDX_E129516320D5ED74 (product_combo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_combo ADD CONSTRAINT FK_FCC77F38A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_product_combo ADD CONSTRAINT FK_FCC77F3856576424 FOREIGN KEY (product_variant_logistic_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_order_item_product_combo ADD CONSTRAINT FK_E1295163E415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id)');
        $this->addSql('ALTER TABLE sylius_order_item_product_combo ADD CONSTRAINT FK_E129516320D5ED74 FOREIGN KEY (product_combo_id) REFERENCES sylius_product_combo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_order_item_product_combo DROP FOREIGN KEY FK_E129516320D5ED74');
        $this->addSql('DROP TABLE sylius_product_combo');
        $this->addSql('DROP TABLE sylius_order_item_product_combo');
    }
}
