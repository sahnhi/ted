<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230105104340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create ask_review_template table to store the template id and the name of the service used to ask a review.
        insert template id for TrustPilot service';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ask_review_template (id INT AUTO_INCREMENT NOT NULL, templateId VARCHAR(255) NOT NULL, serviceName VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO ask_review_template (id, templateId, serviceName) VALUES (1, "6391eff2a3120759a71bb06", "TrustPilot")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ask_review_template');
    }
}
