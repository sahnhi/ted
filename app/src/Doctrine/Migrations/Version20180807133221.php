<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180807133221 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_review_questions (review_id INT NOT NULL, review_question_id INT NOT NULL, INDEX IDX_DC66616E3E2E969B (review_id), INDEX IDX_DC66616E60E11AC3 (review_question_id), PRIMARY KEY(review_id, review_question_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_product_review_question (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, INDEX IDX_E1FF45934584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_product_review_answer (id INT AUTO_INCREMENT NOT NULL, review_id INT DEFAULT NULL, question_id INT DEFAULT NULL, answer VARCHAR(255) NOT NULL, INDEX IDX_D3ABEBBA3E2E969B (review_id), INDEX IDX_D3ABEBBA1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_review_questions ADD CONSTRAINT FK_DC66616E3E2E969B FOREIGN KEY (review_id) REFERENCES sylius_product_review (id)');
        $this->addSql('ALTER TABLE sylius_review_questions ADD CONSTRAINT FK_DC66616E60E11AC3 FOREIGN KEY (review_question_id) REFERENCES sylius_product_review_question (id)');
        $this->addSql('ALTER TABLE sylius_product_review_question ADD CONSTRAINT FK_E1FF45934584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE sylius_product_review_answer ADD CONSTRAINT FK_D3ABEBBA3E2E969B FOREIGN KEY (review_id) REFERENCES sylius_product_review (id)');
        $this->addSql('ALTER TABLE sylius_product_review_answer ADD CONSTRAINT FK_D3ABEBBA1E27F6BF FOREIGN KEY (question_id) REFERENCES sylius_product_review_question (id)');
        $this->addSql('ALTER TABLE sylius_product_review ADD shipping_method_id INT DEFAULT NULL, ADD order_id INT DEFAULT NULL, ADD parent_id INT DEFAULT NULL, ADD NPS INT NOT NULL, ADD locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_product_review ADD CONSTRAINT FK_C7056A995F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE sylius_product_review ADD CONSTRAINT FK_C7056A998D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE sylius_product_review ADD CONSTRAINT FK_C7056A99727ACA70 FOREIGN KEY (parent_id) REFERENCES sylius_product_review (id)');
        $this->addSql('CREATE INDEX IDX_C7056A995F7D6850 ON sylius_product_review (shipping_method_id)');
        $this->addSql('CREATE INDEX IDX_C7056A998D9F6D38 ON sylius_product_review (order_id)');
        $this->addSql('CREATE INDEX IDX_C7056A99727ACA70 ON sylius_product_review (parent_id)');
        $this->addSql('ALTER TABLE sylius_product_variant_packs DROP FOREIGN KEY FK_BA7419251919B217');
        $this->addSql('ALTER TABLE sylius_product_variant_packs ADD CONSTRAINT FK_BA7419251919B217 FOREIGN KEY (pack_id) REFERENCES sylius_pack (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_review_questions DROP FOREIGN KEY FK_DC66616E60E11AC3');
        $this->addSql('ALTER TABLE sylius_product_review_answer DROP FOREIGN KEY FK_D3ABEBBA1E27F6BF');
        $this->addSql('DROP TABLE sylius_review_questions');
        $this->addSql('DROP TABLE sylius_product_review_question');
        $this->addSql('DROP TABLE sylius_product_review_answer');
        $this->addSql('ALTER TABLE sylius_product_review DROP FOREIGN KEY FK_C7056A995F7D6850');
        $this->addSql('ALTER TABLE sylius_product_review DROP FOREIGN KEY FK_C7056A998D9F6D38');
        $this->addSql('ALTER TABLE sylius_product_review DROP FOREIGN KEY FK_C7056A99727ACA70');
        $this->addSql('DROP INDEX IDX_C7056A995F7D6850 ON sylius_product_review');
        $this->addSql('DROP INDEX IDX_C7056A998D9F6D38 ON sylius_product_review');
        $this->addSql('DROP INDEX IDX_C7056A99727ACA70 ON sylius_product_review');
        $this->addSql('ALTER TABLE sylius_product_review DROP shipping_method_id, DROP order_id, DROP parent_id, DROP NPS, DROP locale');
        $this->addSql('ALTER TABLE sylius_product_variant_packs DROP FOREIGN KEY FK_BA7419251919B217');
        $this->addSql('ALTER TABLE sylius_product_variant_packs ADD CONSTRAINT FK_BA7419251919B217 FOREIGN KEY (pack_id) REFERENCES sylius_product_variant (id)');
    }
}
