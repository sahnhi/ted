<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107102246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_ecotax (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, amount_ht INT DEFAULT NULL, amount_ttc INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_product_variant_ecotax (id INT AUTO_INCREMENT NOT NULL, ecotax_id INT DEFAULT NULL, variant_id INT DEFAULT NULL, manufacturerPaid TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_CBE15DB3D00DCE6A (ecotax_id), INDEX IDX_CBE15DB33B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_variant_ecotax ADD CONSTRAINT FK_CBE15DB3D00DCE6A FOREIGN KEY (ecotax_id) REFERENCES sylius_ecotax (id)');
        $this->addSql('ALTER TABLE sylius_product_variant_ecotax ADD CONSTRAINT FK_CBE15DB33B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_variant_ecotax DROP FOREIGN KEY FK_CBE15DB3D00DCE6A');
        $this->addSql('DROP TABLE sylius_ecotax');
        $this->addSql('DROP TABLE sylius_product_variant_ecotax');
    }
}
