<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221017093652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add bidirectional association between product and reason';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE products_reasons (reason_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_C9BEC1F559BB1592 (reason_id), INDEX IDX_C9BEC1F54584665A (product_id), PRIMARY KEY(reason_id, product_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE products_reasons ADD CONSTRAINT FK_C9BEC1F559BB1592 FOREIGN KEY (reason_id) REFERENCES sylius_reason (id)');
        $this->addSql('ALTER TABLE products_reasons ADD CONSTRAINT FK_C9BEC1F54584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products_reasons');
    }
}
