<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230928103729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_bundle_generator ADD shipping_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_bundle_generator ADD CONSTRAINT FK_12D0A5BE9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('CREATE INDEX IDX_12D0A5BE9E2D1A41 ON app_bundle_generator (shipping_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_bundle_generator DROP FOREIGN KEY FK_12D0A5BE9E2D1A41');
        $this->addSql('DROP INDEX IDX_12D0A5BE9E2D1A41 ON app_bundle_generator');
        $this->addSql('ALTER TABLE app_bundle_generator DROP shipping_category_id');
    }
}
