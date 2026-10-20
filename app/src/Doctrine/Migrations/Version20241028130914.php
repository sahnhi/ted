<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241028130914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set original price to null';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_channel_pricing SET original_price = NULL WHERE appliedPromotions IS NULL;');
    }

    public function down(Schema $schema): void
    {
        // no revert possible
    }
}
