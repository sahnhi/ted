<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220913072207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_bundle_association (id INT AUTO_INCREMENT NOT NULL, bundle_id INT DEFAULT NULL, variant_id INT DEFAULT NULL, price INT DEFAULT 0 NOT NULL, quantity INT DEFAULT 0 NOT NULL, INDEX IDX_C997FCA1F1FAD9D3 (bundle_id), INDEX IDX_C997FCA13B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_bundle_association ADD CONSTRAINT FK_C997FCA1F1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_bundle_association ADD CONSTRAINT FK_C997FCA13B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_bundle_association');
    }
}
