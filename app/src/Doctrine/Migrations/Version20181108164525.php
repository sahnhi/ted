<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181108164525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_product_option_value_locale_default (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, product_option_id INT DEFAULT NULL, locale_id INT DEFAULT NULL, product_option_value_id INT DEFAULT NULL, INDEX IDX_278549594584665A (product_id), INDEX IDX_27854959C964ABE2 (product_option_id), INDEX IDX_27854959E559DFD1 (locale_id), INDEX IDX_27854959EBDCCF9B (product_option_value_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_option_value_locale_default ADD CONSTRAINT FK_278549594584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE sylius_product_option_value_locale_default ADD CONSTRAINT FK_27854959C964ABE2 FOREIGN KEY (product_option_id) REFERENCES sylius_product_option (id)');
        $this->addSql('ALTER TABLE sylius_product_option_value_locale_default ADD CONSTRAINT FK_27854959E559DFD1 FOREIGN KEY (locale_id) REFERENCES sylius_locale (id)');
        $this->addSql('ALTER TABLE sylius_product_option_value_locale_default ADD CONSTRAINT FK_27854959EBDCCF9B FOREIGN KEY (product_option_value_id) REFERENCES sylius_product_option_value (id)');
        $this->addSql('DROP TABLE sylius_product_option_locale_default');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_product_option_locale_default (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, product_option_id INT DEFAULT NULL, locale_id INT DEFAULT NULL, product_default_option_id INT DEFAULT NULL, INDEX IDX_2D377D314584665A (product_id), INDEX IDX_2D377D31C964ABE2 (product_option_id), INDEX IDX_2D377D31E559DFD1 (locale_id), INDEX IDX_2D377D318223079B (product_default_option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_option_locale_default ADD CONSTRAINT FK_2D377D314584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE sylius_product_option_locale_default ADD CONSTRAINT FK_2D377D318223079B FOREIGN KEY (product_default_option_id) REFERENCES sylius_product_option (id)');
        $this->addSql('ALTER TABLE sylius_product_option_locale_default ADD CONSTRAINT FK_2D377D31C964ABE2 FOREIGN KEY (product_option_id) REFERENCES sylius_product_option (id)');
        $this->addSql('ALTER TABLE sylius_product_option_locale_default ADD CONSTRAINT FK_2D377D31E559DFD1 FOREIGN KEY (locale_id) REFERENCES sylius_locale (id)');
        $this->addSql('DROP TABLE sylius_product_option_value_locale_default');
    }
}
