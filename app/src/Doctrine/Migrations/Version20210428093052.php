<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210428093052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_review_question ADD questionType VARCHAR(255) NOT NULL, ADD position INT NOT NULL');
        $this->addSql('CREATE TABLE sylius_product_review_answer_choices (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_questions_answer_choices (answer_choice_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_42B9FC7A479B907 (answer_choice_id), INDEX IDX_42B9FC71E27F6BF (question_id), PRIMARY KEY(answer_choice_id, question_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_product_review_answer_choices_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_322411F82C2AC5D3 (translatable_id), UNIQUE INDEX sylius_product_review_answer_choices_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_questions_answer_choices ADD CONSTRAINT FK_42B9FC7A479B907 FOREIGN KEY (answer_choice_id) REFERENCES sylius_product_review_answer_choices (id)');
        $this->addSql('ALTER TABLE sylius_questions_answer_choices ADD CONSTRAINT FK_42B9FC71E27F6BF FOREIGN KEY (question_id) REFERENCES sylius_product_review_question (id)');
        $this->addSql('ALTER TABLE sylius_product_review_answer_choices_translation ADD CONSTRAINT FK_322411F82C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_product_review_answer_choices (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_review_question DROP questionType, DROP position');
        $this->addSql('ALTER TABLE sylius_questions_answer_choices DROP FOREIGN KEY FK_42B9FC7A479B907');
        $this->addSql('ALTER TABLE sylius_product_review_answer_choices_translation DROP FOREIGN KEY FK_322411F82C2AC5D3');
        $this->addSql('DROP TABLE sylius_product_review_answer_choices');
        $this->addSql('DROP TABLE sylius_questions_answer_choices');
        $this->addSql('DROP TABLE sylius_product_review_answer_choices_translation');
    }
}
