<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181004154631 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_osteopath DROP FOREIGN KEY FK_62A56445AA1164F');
        $this->addSql('DROP INDEX UNIQ_62A56445AA1164F ON sylius_osteopath');
        $this->addSql('ALTER TABLE sylius_osteopath CHANGE payment_method_id sylius_address_id INT DEFAULT NULL, CHANGE email name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sylius_osteopath ADD CONSTRAINT FK_62A5644D0E83791 FOREIGN KEY (sylius_address_id) REFERENCES sylius_address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62A5644D0E83791 ON sylius_osteopath (sylius_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_osteopath DROP FOREIGN KEY FK_62A5644D0E83791');
        $this->addSql('DROP INDEX UNIQ_62A5644D0E83791 ON sylius_osteopath');
        $this->addSql('ALTER TABLE sylius_osteopath CHANGE sylius_address_id payment_method_id INT DEFAULT NULL, CHANGE name email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE sylius_osteopath ADD CONSTRAINT FK_62A56445AA1164F FOREIGN KEY (payment_method_id) REFERENCES sylius_address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62A56445AA1164F ON sylius_osteopath (payment_method_id)');
    }
}
