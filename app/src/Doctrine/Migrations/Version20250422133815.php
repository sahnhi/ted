<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250422133815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set NULL order link on product_review when deleting order';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_review DROP FOREIGN KEY FK_C7056A998D9F6D38');
        $this->addSql('ALTER TABLE sylius_product_review ADD CONSTRAINT FK_C7056A998D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_review DROP FOREIGN KEY FK_C7056A998D9F6D38');
        $this->addSql('ALTER TABLE sylius_product_review ADD CONSTRAINT FK_C7056A998D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
    }
}
