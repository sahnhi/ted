<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250417152439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_C7056A992B36786B4180C6987B00651C ON sylius_product_review (title, locale, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_C7056A992B36786B4180C6987B00651C ON sylius_product_review');
    }
}
