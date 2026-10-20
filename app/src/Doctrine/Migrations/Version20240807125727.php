<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240807125727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to sylius_order table when searching on number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_6196A1F9D983DC9243625D9F ON sylius_order (parent_number, updated_at)');
        $this->addSql('CREATE INDEX IDX_6196A1F996901F5443625D9F ON sylius_order (number, updated_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6196A1F9D983DC9243625D9F ON sylius_order');
        $this->addSql('DROP INDEX IDX_6196A1F996901F5443625D9F ON sylius_order');
    }
}
