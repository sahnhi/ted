<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240319111749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index to order.checkoutCompletedAt and state column to speed up admin panel.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_6196A1F955A0F9B ON sylius_order (checkout_completed_at)');
        $this->addSql('CREATE INDEX IDX_6196A1F9A393D2FB ON sylius_order (state)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6196A1F955A0F9B ON sylius_order');
        $this->addSql('DROP INDEX IDX_6196A1F9A393D2FB ON sylius_order');
    }
}
