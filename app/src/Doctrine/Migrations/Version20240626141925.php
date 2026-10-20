<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626141925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_variant ADD product_feed_file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD CONSTRAINT FK_A29B523A47D4F72 FOREIGN KEY (product_feed_file_id) REFERENCES app_product_feed_file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A29B523A47D4F72 ON sylius_product_variant (product_feed_file_id)');
        $this->addSql('ALTER TABLE sylius_product_variant RENAME INDEX uniq_a29b523570a939a TO UNIQ_A29B52348974D54');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_variant DROP FOREIGN KEY FK_A29B523A47D4F72');
        $this->addSql('DROP INDEX UNIQ_A29B523A47D4F72 ON sylius_product_variant');
        $this->addSql('ALTER TABLE sylius_product_variant DROP product_feed_file_id');
        $this->addSql('ALTER TABLE sylius_product_variant RENAME INDEX uniq_a29b52348974d54 TO UNIQ_A29B523570A939A');
    }
}
