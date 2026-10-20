<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220126094917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_combo DROP INDEX UNIQ_FCC77F3856576424, ADD INDEX IDX_FCC77F3856576424 (product_variant_logistic_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_combo DROP INDEX IDX_FCC77F3856576424, ADD UNIQUE INDEX UNIQ_FCC77F3856576424 (product_variant_logistic_id)');
    }
}
