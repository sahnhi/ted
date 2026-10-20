<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240411090126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes for odoo_sent, payment_state and odoo_sent, shipping_state in sylius_order table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_6196A1F994EDD8B6F4DAE449 ON sylius_order (odoo_sent, payment_state)');
        $this->addSql('CREATE INDEX IDX_6196A1F994EDD8B64D0C0F41 ON sylius_order (odoo_sent, shipping_state)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6196A1F994EDD8B6F4DAE449 ON sylius_order');
        $this->addSql('DROP INDEX IDX_6196A1F994EDD8B64D0C0F41 ON sylius_order');
    }
}
