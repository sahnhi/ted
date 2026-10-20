<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240826150351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Shipping Method Category Translation Claim Field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_translation ADD claims LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_translation DROP claims');
    }
}
