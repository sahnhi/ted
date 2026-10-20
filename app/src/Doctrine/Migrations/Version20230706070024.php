<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230706070024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add activate fields to enable/disable a specific reason';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_reason ADD enable TINYINT(1) NOT NULL DEFAULT \'1\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_reason DROP enable');
    }
}
