<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514084155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove useless bi-directional relation between product and product_cms_faq, and add delete cascade to product_cms_faq_question';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_faq DROP FOREIGN KEY FK_22F9828E4584665A');
        $this->addSql('DROP INDEX UNIQ_22F9828E4584665A ON app_product_cms_faq');
        $this->addSql('ALTER TABLE sylius_product ADD product_cms_faq_id INT DEFAULT NULL');
        $this->addSql('UPDATE sylius_product SET product_cms_faq_id = (SELECT id FROM app_product_cms_faq WHERE product_id = sylius_product.id)');
        $this->addSql('ALTER TABLE app_product_cms_faq DROP product_id');
        $this->addSql('ALTER TABLE app_product_cms_faq_question DROP FOREIGN KEY FK_9C3C8E6373134CDA');
        $this->addSql('DROP INDEX IDX_9C3C8E6373134CDA ON app_product_cms_faq_question');
        $this->addSql('ALTER TABLE app_product_cms_faq_question CHANGE productcmsfaq_id product_cms_faq_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_cms_faq_question ADD CONSTRAINT FK_9C3C8E63202F8891 FOREIGN KEY (product_cms_faq_id) REFERENCES app_product_cms_faq (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9C3C8E63202F8891 ON app_product_cms_faq_question (product_cms_faq_id)');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74202F8891 FOREIGN KEY (product_cms_faq_id) REFERENCES app_product_cms_faq (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74202F8891 ON sylius_product (product_cms_faq_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_faq ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_cms_faq ADD CONSTRAINT FK_22F9828E4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_22F9828E4584665A ON app_product_cms_faq (product_id)');
        $this->addSql('ALTER TABLE app_product_cms_faq_question DROP FOREIGN KEY FK_9C3C8E63202F8891');
        $this->addSql('DROP INDEX IDX_9C3C8E63202F8891 ON app_product_cms_faq_question');
        $this->addSql('ALTER TABLE app_product_cms_faq_question CHANGE product_cms_faq_id productCMSFaq_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_cms_faq_question ADD CONSTRAINT FK_9C3C8E6373134CDA FOREIGN KEY (productCMSFaq_id) REFERENCES app_product_cms_faq (id)');
        $this->addSql('CREATE INDEX IDX_9C3C8E6373134CDA ON app_product_cms_faq_question (productCMSFaq_id)');
    }
}
