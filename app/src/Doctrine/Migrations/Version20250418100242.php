<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250418100242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on product created_at column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_677B9B748B8E8428 ON sylius_product (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_677B9B748B8E8428 ON sylius_product');
    }
}
