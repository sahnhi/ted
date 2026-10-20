<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802124231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = "chronopost" WHERE code = "CHRONOPOST"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = NULL WHERE code = "CHRONOPOST"');
    }
}
