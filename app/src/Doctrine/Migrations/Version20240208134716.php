<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240208134716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new odoo fields to order';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order ADD odoo_state VARCHAR(255) NOT NULL, CHANGE odoo_sent odoo_sent INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_6196A1F92EB5F6FC ON sylius_order (odoo_state)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order DROP odoo_state, CHANGE odoo_sent odoo_sent INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX IDX_6196A1F92EB5F6FC ON sylius_order');
    }
}
