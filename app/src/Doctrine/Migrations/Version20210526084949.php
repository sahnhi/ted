<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526084949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_review_answer ADD answer_choice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_review_answer ADD CONSTRAINT FK_D3ABEBBAA479B907 FOREIGN KEY (answer_choice_id) REFERENCES sylius_product_review_answer_choices (id)');
        $this->addSql('CREATE INDEX IDX_D3ABEBBAA479B907 ON sylius_product_review_answer (answer_choice_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_review_answer DROP FOREIGN KEY FK_D3ABEBBAA479B907');
        $this->addSql('DROP INDEX IDX_D3ABEBBAA479B907 ON sylius_product_review_answer');
        $this->addSql('ALTER TABLE sylius_product_review_answer DROP answer_choice_id');
    }
}
