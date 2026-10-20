<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240410103745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on odoo_sent';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_6196A1F994EDD8B6 ON sylius_order (odoo_sent)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6196A1F994EDD8B6 ON sylius_order');
    }
}
