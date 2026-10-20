<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240811223321 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_translation ADD sub_info VARCHAR(255) DEFAULT NULL, ADD sub_info_bis VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_translation DROP sub_info, DROP sub_info_bis');
        $this->addSql('ALTER TABLE sylius_product ADD product_cms_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74DCF63D5F FOREIGN KEY (product_cms_id) REFERENCES app_product_cms (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74DCF63D5F ON sylius_product (product_cms_id)');
    }
}
