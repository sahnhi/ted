<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241119092403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove promotion order_total promotion actions and rules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM sylius_promotion_action WHERE type = "order_total_percentage_discount"');
        $this->addSql('DELETE FROM sylius_promotion_rule WHERE type = "order_total_greater_or_equal_than"');
    }

    public function down(Schema $schema): void
    {
    }
}
