<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240723125009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_file_channable_row DROP FOREIGN KEY FK_F38A08DC24D8F64E');
        $this->addSql('ALTER TABLE app_file_channable_row ADD CONSTRAINT FK_F38A08DC24D8F64E FOREIGN KEY (channable_file_log_id) REFERENCES app_channable_file_log (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_file_channable_row DROP FOREIGN KEY FK_F38A08DC24D8F64E');
        $this->addSql('ALTER TABLE app_file_channable_row ADD CONSTRAINT FK_F38A08DC24D8F64E FOREIGN KEY (channable_file_log_id) REFERENCES app_channable_file_log (id)');
    }
}
