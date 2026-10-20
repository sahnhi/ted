<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230725143833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_bundle_association_price (id INT AUTO_INCREMENT NOT NULL, bundle_association_id INT DEFAULT NULL, price INT DEFAULT 0 NOT NULL, createdAt DATETIME NOT NULL, INDEX IDX_A19DC58070C54D07 (bundle_association_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_bundle_association_price ADD CONSTRAINT FK_A19DC58070C54D07 FOREIGN KEY (bundle_association_id) REFERENCES app_bundle_association (id)');
        $this->addSql('INSERT INTO app_bundle_association_price (price, createdAt, bundle_association_id) SELECT ba.price, NOW(), ba.id FROM app_bundle_association ba');
        $this->addSql('ALTER TABLE app_bundle_association DROP price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_bundle_association ADD price INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE app_bundle_association ba INNER JOIN app_bundle_association_price bap on ba.id = bap.bundle_association_id SET ba.price = bap.price');
        $this->addSql('DROP TABLE app_bundle_association_price');
    }
}
