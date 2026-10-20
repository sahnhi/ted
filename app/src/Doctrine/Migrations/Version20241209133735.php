<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241209133735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add index on productReview.status';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_C7056A997B00651C ON sylius_product_review (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_C7056A997B00651C ON sylius_product_review');
    }
}
