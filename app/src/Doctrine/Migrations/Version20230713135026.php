<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230713135026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add field destination to track the return';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order_return_item ADD destination VARCHAR(255) NOT NULL DEFAULT \'untrack\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order_return_item DROP destination');
    }
}
