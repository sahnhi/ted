<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240724074746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_file_channable_row ADD variant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_file_channable_row ADD CONSTRAINT FK_F38A08DC3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_file_channable_row DROP FOREIGN KEY FK_F38A08DC3B69A9AF');
        $this->addSql('DROP INDEX IDX_F38A08DC3B69A9AF ON app_file_channable_row');
    }
}
