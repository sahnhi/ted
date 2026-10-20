<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180808185247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_familiarity_translation (id INT AUTO_INCREMENT NOT NULL, wording VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_familiarity DROP wording');
        $this->addSql('ALTER TABLE sylius_familiarity_translation ADD translatable_id INT NOT NULL, ADD locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_familiarity_translation ADD CONSTRAINT FK_C4DA941B2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_familiarity (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C4DA941B2C2AC5D3 ON sylius_familiarity_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX sylius_familiarity_translation_uniq_trans ON sylius_familiarity_translation (translatable_id, locale)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_familiarity_translation DROP FOREIGN KEY FK_C4DA941B2C2AC5D3');
        $this->addSql('DROP INDEX IDX_C4DA941B2C2AC5D3 ON sylius_familiarity_translation');
        $this->addSql('DROP INDEX sylius_familiarity_translation_uniq_trans ON sylius_familiarity_translation');
        $this->addSql('ALTER TABLE sylius_familiarity_translation DROP translatable_id, DROP locale');
        $this->addSql('DROP TABLE sylius_familiarity_translation');
        $this->addSql('ALTER TABLE sylius_familiarity ADD wording VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
