<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911202954 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_focus ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_cms_focus ADD CONSTRAINT FK_8356CAD74584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8356CAD74584665A ON app_product_cms_focus (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_focus DROP FOREIGN KEY FK_8356CAD74584665A');
        $this->addSql('DROP INDEX UNIQ_8356CAD74584665A ON app_product_cms_focus');
        $this->addSql('ALTER TABLE app_product_cms_focus DROP product_id');
        $this->addSql('ALTER TABLE sylius_product ADD product_cms_focus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74B6CAA5D4 FOREIGN KEY (product_cms_focus_id) REFERENCES app_product_cms_focus (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74B6CAA5D4 ON sylius_product (product_cms_focus_id)');
    }

    public function isTransactional(): bool
    {
        return true;
    }
}
