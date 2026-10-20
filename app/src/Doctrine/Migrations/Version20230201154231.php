<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230201154231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes on adjustment.type and adjustment.created_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX tax_idx ON sylius_adjustment (type)');
        $this->addSql('CREATE INDEX created_at_idx ON sylius_adjustment (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX tax_idx ON sylius_adjustment');
        $this->addSql('DROP INDEX created_at_idx ON sylius_adjustment');
    }
}
