<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221110095216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_product_google_rss ADD variant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_google_rss ADD CONSTRAINT FK_C026F3403B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C026F3403B69A9AF ON app_product_google_rss (variant_id)');
        $this->addSql('ALTER TABLE app_product_google_rss ADD product_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_product_google_rss DROP FOREIGN KEY FK_C026F3403B69A9AF');
        $this->addSql('DROP INDEX UNIQ_C026F3403B69A9AF ON app_product_google_rss');
        $this->addSql('ALTER TABLE app_product_google_rss DROP variant_id');
        $this->addSql('ALTER TABLE app_product_google_rss DROP product_url');
    }
}
