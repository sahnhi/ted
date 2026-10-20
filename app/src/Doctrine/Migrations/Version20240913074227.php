<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240913074227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove Unique Index On ShippingMethodCategoryService Related Products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_service_restricted_product DROP INDEX UNIQ_FF106BF4584665A, ADD INDEX IDX_FF106BF4584665A (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_service_restricted_product DROP INDEX IDX_FF106BF4584665A, ADD UNIQUE INDEX UNIQ_FF106BF4584665A (product_id)');
    }
}
