<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228132540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order DROP order_type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order ADD order_type VARCHAR(20) DEFAULT NULL');
    }
}
