<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325145438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on product_id and status on ProductReview table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_C7056A994584665A7B00651C ON sylius_product_review (product_id, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_C7056A994584665A7B00651C ON sylius_product_review');
    }
}
