<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240813083553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_translation ADD product_details_title VARCHAR(255) DEFAULT NULL, ADD product_details_sub_title VARCHAR(255) DEFAULT NULL, ADD product_details_sub_claim TINYTEXT DEFAULT NULL, ADD product_details_sub_title_bis VARCHAR(255) DEFAULT NULL, ADD product_details_sub_claim_bis TINYTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_translation DROP product_details_title, DROP product_details_sub_title, DROP product_details_sub_claim, DROP product_details_sub_title_bis, DROP product_details_sub_claim_bis');
    }
}
