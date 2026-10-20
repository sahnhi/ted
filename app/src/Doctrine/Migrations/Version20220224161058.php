<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220224161058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method_translation ADD delivery_conditions LONGTEXT DEFAULT NULL');
        $this->addSql("UPDATE sylius_shipping_method_translation SET delivery_conditions = 'Votre créneau de livraison sera affiné la veille de la livraison, par sms. Nous ne pourrons pas modifier ce créneau de notre côté.
En cas de commande de plusieurs articles il est possible que votre livraison se fasse en plusieurs colis. Si c''est le cas vous trouverez un lien de suivi par colis en cliquant ci dessous.
En cas d''absence, vous serez contacté par sms pour vous proposer une nouvelle date de livraison ou la livraison de votre colis en point relais.' WHERE translatable_id = (SELECT id FROM sylius_shipping_method WHERE code = 'DPD') AND locale = 'fr_FR'");
        $this->addSql("UPDATE sylius_shipping_method_translation SET delivery_conditions = 'La livraison sera effectuée en bas d''immeuble, le créneau de 8h à 13h30 ne sera pas affiné. Vous recevrez un lien, le jour de la livraison pour suivre le livreur.
En cas d''absence, vous pouvez reprogrammer une livraison depuis ce lien.' WHERE translatable_id = (SELECT id FROM sylius_shipping_method WHERE code = 'FRANCE-EXPRESS') AND locale = 'fr_FR'");
        $this->addSql("UPDATE sylius_shipping_method_translation SET delivery_conditions = 'Les créneaux proposées ne sont pas affinés, vous serez contacté par sms pour suivre le livreur avant votre livraison.
En cas d''absence, vous pouvez nous envoyer un mail (hello@tediber.com) pour reprogrammer la livraison.' WHERE translatable_id = (SELECT id FROM sylius_shipping_method WHERE code = 'E-DELIV') AND locale = 'fr_FR'");
        $this->addSql("UPDATE sylius_shipping_method_translation SET delivery_conditions = 'Votre créneau de livraison sera affiné la veille de la livraison, par sms. Nous ne pourrons pas modifier ce créneau de notre côté.
En cas d''absence, le transporteur vous contactera pour vous proposer une nouvelle date de livraison.' WHERE translatable_id = (SELECT id FROM sylius_shipping_method WHERE code = 'VIR') AND locale = 'fr_FR'");
        $this->addSql("UPDATE sylius_shipping_method_translation SET delivery_conditions = 'La livraison aura lieu en bas d''immeuble, le créneau de 9h à 18h ne sera pas affiné.' WHERE translatable_id = (SELECT id FROM sylius_shipping_method WHERE code = 'DHL') AND locale = 'fr_FR'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method_translation DROP delivery_conditions');
    }
}
