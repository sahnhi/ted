<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250418094814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on product reviews created_at column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_C7056A998B8E8428 ON sylius_product_review (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_C7056A998B8E8428 ON sylius_product_review');
    }
}
