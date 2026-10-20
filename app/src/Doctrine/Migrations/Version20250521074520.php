<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250521074520 extends AbstractMigration
{
    private const SIZES_TO_RENAME = [
        '140 cm x 200 cm' => '140 x 200 cm',
        '200 cm x 200 cm' => '200 x 200 cm',
        '220 cm x 220 cm' => '220 x 220 cm',
        '240 cm x 220 cm' => '240 x 220 cm',
        '250 cm x 220 cm' => '250 x 220 cm',
        '260 cm x 240 cm' => '260 x 240 cm',
        '280 cm x 240 cm' => '280 x 240 cm',
    ];

    public function getDescription(): string
    {
        return 'Normalize blankets and sheets sizes option values';
    }

    public function up(Schema $schema): void
    {
        foreach (self::SIZES_TO_RENAME as $oldSize => $newSize) {
            $this->addSql('UPDATE sylius_product_option_value_translation SET value = ? WHERE value = ?', [
                $newSize,
                $oldSize,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::SIZES_TO_RENAME as $newSize => $oldSize) {
            $this->addSql('UPDATE sylius_product_option_value_translation SET value = ? WHERE value = ?', [
                $oldSize,
                $newSize,
            ]);
        }
    }
}
