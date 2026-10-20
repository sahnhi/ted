<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250211101940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update return product translation names based on non return name.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TEMPORARY TABLE temp_names AS
            SELECT p2.code, pt2.translatable_id, pt2.name
            FROM sylius_product_translation pt2
            INNER JOIN sylius_product p2 ON p2.id = pt2.translatable_id
            WHERE p2.code NOT LIKE \'return-%\'
            AND pt2.locale = \'fr_FR\';
        ');

        $this->addSql('
            UPDATE sylius_product_translation pt
            INNER JOIN sylius_product p ON p.id = pt.translatable_id
            INNER JOIN temp_names tn ON SUBSTRING(p.code, 8) = tn.code
            SET pt.name = tn.name
            WHERE p.code LIKE \'return-%\'
            AND pt.locale = \'fr_FR\';
        ');

        $this->addSql('DROP TEMPORARY TABLE temp_names;');
    }

    public function down(Schema $schema): void
    {
        // no reverse possible
    }
}
