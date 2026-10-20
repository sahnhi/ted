<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202085243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE setono_sylius_gift_card__channel_configuration (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, configuration_id INT NOT NULL, locale_id INT NOT NULL, INDEX IDX_571F956572F5A1AA (channel_id), INDEX IDX_571F956573F32DD8 (configuration_id), INDEX IDX_571F9565E559DFD1 (locale_id), UNIQUE INDEX unique_channel_locale (channel_id, locale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_gift_card__configuration (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, is_default TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_ADC64AD077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_gift_card__configuration_image (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, INDEX IDX_CDFECBED7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_gift_card__gift_card (id INT AUTO_INCREMENT NOT NULL, order_item_unit_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, channel_id INT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, amount INT NOT NULL, initialAmount INT NOT NULL, currencyCode VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_A5B7155477153098 (code), UNIQUE INDEX UNIQ_A5B71554F720C233 (order_item_unit_id), INDEX IDX_A5B715549395C3F3 (customer_id), INDEX IDX_A5B7155472F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setono_sylius_gift_card__order_gift_cards (order_id INT NOT NULL, gift_card_id INT NOT NULL, INDEX IDX_429AE86C8D9F6D38 (order_id), INDEX IDX_429AE86C2696A98F (gift_card_id), PRIMARY KEY(order_id, gift_card_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__channel_configuration ADD CONSTRAINT FK_571F956572F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__channel_configuration ADD CONSTRAINT FK_571F956573F32DD8 FOREIGN KEY (configuration_id) REFERENCES setono_sylius_gift_card__configuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__channel_configuration ADD CONSTRAINT FK_571F9565E559DFD1 FOREIGN KEY (locale_id) REFERENCES sylius_locale (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__configuration_image ADD CONSTRAINT FK_CDFECBED7E3C61F9 FOREIGN KEY (owner_id) REFERENCES setono_sylius_gift_card__configuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__gift_card ADD CONSTRAINT FK_A5B71554F720C233 FOREIGN KEY (order_item_unit_id) REFERENCES sylius_order_item_unit (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__gift_card ADD CONSTRAINT FK_A5B715549395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__gift_card ADD CONSTRAINT FK_A5B7155472F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C2696A98F FOREIGN KEY (gift_card_id) REFERENCES setono_sylius_gift_card__gift_card (id)');
        $this->addSql('ALTER TABLE sylius_product ADD gift_card TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE setono_sylius_gift_card__channel_configuration DROP FOREIGN KEY FK_571F956573F32DD8');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__configuration_image DROP FOREIGN KEY FK_CDFECBED7E3C61F9');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards DROP FOREIGN KEY FK_429AE86C2696A98F');
        $this->addSql('DROP TABLE setono_sylius_gift_card__channel_configuration');
        $this->addSql('DROP TABLE setono_sylius_gift_card__configuration');
        $this->addSql('DROP TABLE setono_sylius_gift_card__configuration_image');
        $this->addSql('DROP TABLE setono_sylius_gift_card__gift_card');
        $this->addSql('DROP TABLE setono_sylius_gift_card__order_gift_cards');
        $this->addSql('ALTER TABLE sylius_product DROP gift_card');
    }
}
