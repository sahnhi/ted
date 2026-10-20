<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250211145115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update average_rating for specific products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE tediber.sylius_product AS target
            JOIN tediber.sylius_product AS source ON source.id = 18
            SET target.average_rating = source.average_rating
            WHERE target.id = 279;
        ');

        $this->addSql('
            UPDATE tediber.sylius_product AS target
            JOIN tediber.sylius_product AS source ON source.id = 324
            SET target.average_rating = source.average_rating
            WHERE target.id = 327;
        ');

        $this->addSql('
            UPDATE tediber.sylius_product AS target
            JOIN tediber.sylius_product AS source ON source.id = 86
            SET target.average_rating = source.average_rating
            WHERE target.id = 282;
        ');

        $this->addSql('
            UPDATE tediber.sylius_product AS target
            JOIN tediber.sylius_product AS source ON source.id = 241
            SET target.average_rating = source.average_rating
            WHERE target.id = 286;
        ');
    }

    public function down(Schema $schema): void
    {
        // no revert
    }
}
