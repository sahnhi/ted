<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190114093453 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customer DROP INDEX UNIQ_7E82D5E65AA1164F, ADD INDEX IDX_7E82D5E65AA1164F (payment_method_id)');
        $this->addSql('ALTER TABLE sylius_customer DROP INDEX UNIQ_7E82D5E6E24EDBEF, ADD INDEX IDX_7E82D5E6E24EDBEF (osteopath_id)');
        $this->addSql('ALTER TABLE sylius_customer DROP INDEX UNIQ_7E82D5E66A5458E8, ADD INDEX IDX_7E82D5E66A5458E8 (friend_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_customer DROP INDEX IDX_7E82D5E65AA1164F, ADD UNIQUE INDEX UNIQ_7E82D5E65AA1164F (payment_method_id)');
        $this->addSql('ALTER TABLE sylius_customer DROP INDEX IDX_7E82D5E6E24EDBEF, ADD UNIQUE INDEX UNIQ_7E82D5E6E24EDBEF (osteopath_id)');
        $this->addSql('ALTER TABLE sylius_customer DROP INDEX IDX_7E82D5E66A5458E8, ADD UNIQUE INDEX UNIQ_7E82D5E66A5458E8 (friend_id)');
    }
}
