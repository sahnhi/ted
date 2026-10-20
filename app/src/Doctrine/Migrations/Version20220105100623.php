<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220105100623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order ADD review_type VARCHAR(20) DEFAULT NULL');
        $this->addSql("UPDATE sylius_order SET review_type = '" . Order::REVIEW_TYPE_MANDRILL . "' WHERE review_email_sent=1");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_order DROP review_type');
    }
}
