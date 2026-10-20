<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250506073929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete fastmag line when order is deleted';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fastmag DROP FOREIGN KEY FK_3B221E4722436D7');
        $this->addSql('ALTER TABLE fastmag ADD CONSTRAINT FK_3B221E4722436D7 FOREIGN KEY (sylius_order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fastmag DROP FOREIGN KEY FK_3B221E4722436D7');
        $this->addSql('ALTER TABLE fastmag ADD CONSTRAINT FK_3B221E4722436D7 FOREIGN KEY (sylius_order_id) REFERENCES sylius_order (id)');
    }
}
