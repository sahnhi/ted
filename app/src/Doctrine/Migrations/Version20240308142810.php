<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Model\ProductReferenceInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308142810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change repossess product variant codes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sql: sprintf(
            "UPDATE sylius_product_variant SET code = CASE
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    ELSE code
                END;",
            ProductReferenceInterface::REP_MAT, ProductReferenceInterface::OLD_REP_MAT,
            ProductReferenceInterface::REP_SOM, ProductReferenceInterface::OLD_REP_SOM,
            ProductReferenceInterface::REP_SOMC, ProductReferenceInterface::OLD_REP_SOMC,
            ProductReferenceInterface::REP_SOM_BOIS, ProductReferenceInterface::OLD_REP_SOM_BOIS,
            ProductReferenceInterface::REP_SOM_LAT, ProductReferenceInterface::OLD_REP_SOM_LAT,
            ProductReferenceInterface::REP_TET, ProductReferenceInterface::OLD_REP_TET,
            ProductReferenceInterface::REP_SOF, ProductReferenceInterface::OLD_REP_SOF,
            ProductReferenceInterface::REP_TPR, ProductReferenceInterface::OLD_REP_TPR,
            ProductReferenceInterface::REP_CANAP, ProductReferenceInterface::OLD_REP_CANAP
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sql: sprintf(
            "UPDATE sylius_product_variant SET code = CASE
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    WHEN code = '%s' THEN '%s'
                    ELSE code
                END;",
            ProductReferenceInterface::OLD_REP_MAT, ProductReferenceInterface::REP_MAT,
            ProductReferenceInterface::OLD_REP_SOM, ProductReferenceInterface::REP_SOM,
            ProductReferenceInterface::OLD_REP_SOMC, ProductReferenceInterface::REP_SOMC,
            ProductReferenceInterface::OLD_REP_SOM_BOIS, ProductReferenceInterface::REP_SOM_BOIS,
            ProductReferenceInterface::OLD_REP_SOM_LAT, ProductReferenceInterface::REP_SOM_LAT,
            ProductReferenceInterface::OLD_REP_TET, ProductReferenceInterface::REP_TET,
            ProductReferenceInterface::OLD_REP_SOF, ProductReferenceInterface::REP_SOF,
            ProductReferenceInterface::OLD_REP_TPR, ProductReferenceInterface::REP_TPR,
            ProductReferenceInterface::OLD_REP_CANAP, ProductReferenceInterface::REP_CANAP
        ));
    }
}
