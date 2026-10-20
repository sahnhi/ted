<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200311144655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_zip_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_zip_code_zone (zip_code_id INT NOT NULL, zone_id INT NOT NULL, INDEX IDX_10B70FC99CEB97F7 (zip_code_id), INDEX IDX_10B70FC99F2C3FAB (zone_id), PRIMARY KEY(zip_code_id, zone_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_zip_code_zone ADD CONSTRAINT FK_10B70FC99CEB97F7 FOREIGN KEY (zip_code_id) REFERENCES sylius_zip_code (id)');
        $this->addSql('ALTER TABLE sylius_zip_code_zone ADD CONSTRAINT FK_10B70FC99F2C3FAB FOREIGN KEY (zone_id) REFERENCES sylius_zone (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_zip_code_zone DROP FOREIGN KEY FK_10B70FC99CEB97F7');
        $this->addSql('DROP TABLE sylius_zip_code');
        $this->addSql('DROP TABLE sylius_zip_code_zone');
    }
}
