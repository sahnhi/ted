<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250430094330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete order store when deleting order';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order_store DROP FOREIGN KEY FK_DB8B55738D9F6D38');
        $this->addSql('ALTER TABLE sylius_order_store ADD CONSTRAINT FK_DB8B55738D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_order_store DROP FOREIGN KEY FK_DB8B55738D9F6D38');
        $this->addSql('ALTER TABLE sylius_order_store ADD CONSTRAINT FK_DB8B55738D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
    }
}
