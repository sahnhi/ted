<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240307094149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Edit values for Odoo state machine rework';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sql: "UPDATE sylius_order SET odoo_state = CASE
            WHEN odoo_sent = -1 THEN 'not_ready'
            WHEN odoo_sent = 0 THEN 'ready'
            WHEN odoo_sent = 1 THEN 'created'
            WHEN odoo_sent = 2 THEN 'destocked'
            WHEN odoo_sent = 3 THEN 'shipped'
            WHEN odoo_sent = 4 THEN 'refunded'
            WHEN odoo_sent = 5 THEN 'cancelled'
            ELSE 'ready'
            END;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sql: "UPDATE sylius_order SET odoo_sent = CASE
                WHEN odoo_state = 'not_ready' THEN -1
                WHEN odoo_state = 'ready' THEN 0
                WHEN odoo_state = 'created' THEN 1
                WHEN odoo_state = 'destocked' THEN 2
                WHEN odoo_state = 'shipped' THEN 3
                WHEN odoo_state = 'refunded' THEN 4
                WHEN odoo_state = 'cancelled' THEN 5
                ELSE 0
                END;");
    }
}
