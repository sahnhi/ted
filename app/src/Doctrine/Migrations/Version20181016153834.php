<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181016153834 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_product CHANGE transporteur transporteur VARCHAR(255) DEFAULT NULL, CHANGE ville_livraison ville_livraison VARCHAR(255) DEFAULT NULL, CHANGE pays_livraison pays_livraison VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_product CHANGE transporteur transporteur VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE ville_livraison ville_livraison VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE pays_livraison pays_livraison VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
