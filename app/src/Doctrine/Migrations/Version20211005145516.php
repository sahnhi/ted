<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211005145516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_order_return_item (id INT AUTO_INCREMENT NOT NULL, variant_id INT DEFAULT NULL, quantity INT DEFAULT NULL, orderReturn_id INT NOT NULL, INDEX IDX_4E2AFB4D6FBD33A7 (orderReturn_id), INDEX IDX_4E2AFB4D3B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_order_return_item ADD CONSTRAINT FK_4E2AFB4D6FBD33A7 FOREIGN KEY (orderReturn_id) REFERENCES sylius_order_return (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_order_return_item ADD CONSTRAINT FK_4E2AFB4D3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_order_return_item');
    }
}
