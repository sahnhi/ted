<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230201142255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify the field reason to non nullable for table quality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quality CHANGE reason_id reason_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quality CHANGE reason_id reason_id INT DEFAULT NULL');
    }
}
