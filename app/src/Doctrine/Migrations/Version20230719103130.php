<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719103130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_bundle_generator (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, prefix VARCHAR(255) NOT NULL, suffix VARCHAR(255) NOT NULL, nb_products INT DEFAULT 4 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_generated TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_12D0A5BE4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_bundle_generator_combination (id INT AUTO_INCREMENT NOT NULL, bundle_generator_id INT DEFAULT NULL, already_exists TINYINT(1) DEFAULT \'0\' NOT NULL, will_be_generated TINYINT(1) DEFAULT \'1\' NOT NULL, code VARCHAR(255) DEFAULT NULL, INDEX IDX_4066D11FA29C6485 (bundle_generator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_bundle_generator_combination_product_option_value (bundle_generator_combination_id INT NOT NULL, product_option_value_id INT NOT NULL, INDEX IDX_847D45D63297DD82 (bundle_generator_combination_id), INDEX IDX_847D45D6EBDCCF9B (product_option_value_id), PRIMARY KEY(bundle_generator_combination_id, product_option_value_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_bundle_generator_option (id INT AUTO_INCREMENT NOT NULL, product_option_id INT DEFAULT NULL, product_option_value_id INT DEFAULT NULL, bundle_generator_id INT DEFAULT NULL, position INT DEFAULT NULL, forced_value TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_E9CBB3DEC964ABE2 (product_option_id), INDEX IDX_E9CBB3DEEBDCCF9B (product_option_value_id), INDEX IDX_E9CBB3DEA29C6485 (bundle_generator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_bundle_generator_product (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, bundle_generator_id INT DEFAULT NULL, quantity INT DEFAULT 1 NOT NULL, is_free TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_79F305414584665A (product_id), INDEX IDX_79F30541A29C6485 (bundle_generator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_bundle_generator_variant (id INT AUTO_INCREMENT NOT NULL, variant_id INT DEFAULT NULL, bundle_generator_combination_id INT DEFAULT NULL, bundle_generator_product_id INT DEFAULT NULL, INDEX IDX_5BFABE413B69A9AF (variant_id), INDEX IDX_5BFABE413297DD82 (bundle_generator_combination_id), INDEX IDX_5BFABE41EB30D7D1 (bundle_generator_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_bundle_generator ADD CONSTRAINT FK_12D0A5BE4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_combination ADD CONSTRAINT FK_4066D11FA29C6485 FOREIGN KEY (bundle_generator_id) REFERENCES app_bundle_generator (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_combination_product_option_value ADD CONSTRAINT FK_847D45D63297DD82 FOREIGN KEY (bundle_generator_combination_id) REFERENCES app_bundle_generator_combination (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_combination_product_option_value ADD CONSTRAINT FK_847D45D6EBDCCF9B FOREIGN KEY (product_option_value_id) REFERENCES sylius_product_option_value (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_option ADD CONSTRAINT FK_E9CBB3DEC964ABE2 FOREIGN KEY (product_option_id) REFERENCES sylius_product_option (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_option ADD CONSTRAINT FK_E9CBB3DEEBDCCF9B FOREIGN KEY (product_option_value_id) REFERENCES sylius_product_option_value (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_option ADD CONSTRAINT FK_E9CBB3DEA29C6485 FOREIGN KEY (bundle_generator_id) REFERENCES app_bundle_generator (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_product ADD CONSTRAINT FK_79F305414584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_product ADD CONSTRAINT FK_79F30541A29C6485 FOREIGN KEY (bundle_generator_id) REFERENCES app_bundle_generator (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_variant ADD CONSTRAINT FK_5BFABE413B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_variant ADD CONSTRAINT FK_5BFABE413297DD82 FOREIGN KEY (bundle_generator_combination_id) REFERENCES app_bundle_generator_combination (id)');
        $this->addSql('ALTER TABLE app_bundle_generator_variant ADD CONSTRAINT FK_5BFABE41EB30D7D1 FOREIGN KEY (bundle_generator_product_id) REFERENCES app_bundle_generator_product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_bundle_generator_combination DROP FOREIGN KEY FK_4066D11FA29C6485');
        $this->addSql('ALTER TABLE app_bundle_generator_option DROP FOREIGN KEY FK_E9CBB3DEA29C6485');
        $this->addSql('ALTER TABLE app_bundle_generator_product DROP FOREIGN KEY FK_79F30541A29C6485');
        $this->addSql('ALTER TABLE app_bundle_generator_combination_product_option_value DROP FOREIGN KEY FK_847D45D63297DD82');
        $this->addSql('ALTER TABLE app_bundle_generator_variant DROP FOREIGN KEY FK_5BFABE413297DD82');
        $this->addSql('ALTER TABLE app_bundle_generator_variant DROP FOREIGN KEY FK_5BFABE41EB30D7D1');
        $this->addSql('DROP TABLE app_bundle_generator');
        $this->addSql('DROP TABLE app_bundle_generator_combination');
        $this->addSql('DROP TABLE app_bundle_generator_combination_product_option_value');
        $this->addSql('DROP TABLE app_bundle_generator_option');
        $this->addSql('DROP TABLE app_bundle_generator_product');
        $this->addSql('DROP TABLE app_bundle_generator_variant');
    }
}
