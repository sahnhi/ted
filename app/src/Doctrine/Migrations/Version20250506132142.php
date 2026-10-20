<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250506132142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update gift card order relations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards DROP FOREIGN KEY FK_429AE86C2696A98F');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards DROP FOREIGN KEY FK_429AE86C8D9F6D38');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C2696A98F FOREIGN KEY (gift_card_id) REFERENCES setono_sylius_gift_card__gift_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards DROP FOREIGN KEY FK_429AE86C8D9F6D38');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards DROP FOREIGN KEY FK_429AE86C2696A98F');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id)');
        $this->addSql('ALTER TABLE setono_sylius_gift_card__order_gift_cards ADD CONSTRAINT FK_429AE86C2696A98F FOREIGN KEY (gift_card_id) REFERENCES setono_sylius_gift_card__gift_card (id)');
    }
}
