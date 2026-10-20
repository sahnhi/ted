<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\Review\Seek\Seeker\TrustPilot;
use App\Services\Review\Seek\Seeker\VerifiedReviews;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619083243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ask_review_template ADD `order` SMALLINT NOT NULL, CHANGE templateId templateId VARCHAR(255) DEFAULT NULL');
        $this->addSql(
            'UPDATE ask_review_template SET `order` = 1 WHERE serviceName = :name',
            ['name' => TrustPilot::NAME],
        );
        $this->addSql(
            'INSERT INTO ask_review_template (templateId, serviceName, percentage, `order`) VALUES (null, :name, :percentage, :order) ',
            [
                'name' => VerifiedReviews::NAME,
                'percentage' => 10,
                'order' => 2,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'DELETE FROM ask_review_template WHERE serviceName = :name',
            ['name' => VerifiedReviews::NAME]
        );
        $this->addSql('ALTER TABLE ask_review_template DROP `order`, CHANGE templateId templateId VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
