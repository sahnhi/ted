<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308125645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Product part
        $this->addSql('ALTER TABLE sylius_product ADD productGoogleRss_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74570A939A FOREIGN KEY (productGoogleRss_id) REFERENCES app_product_google_rss (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74570A939A ON sylius_product (productGoogleRss_id)');
        $this->addSql(<<<EOM
           UPDATE sylius_product product
                   INNER JOIN app_product_google_rss rss
                       on rss.product_id = product.id
           SET product.productGoogleRss_id = rss.id
           WHERE 1
          EOM
        );
        $this->addSql('ALTER TABLE app_product_google_rss DROP FOREIGN KEY FK_C026F3404584665A');
        $this->addSql('DROP INDEX UNIQ_C026F3404584665A ON app_product_google_rss');
        $this->addSql('ALTER TABLE app_product_google_rss DROP product_id');

        // ProductVariant part
        $this->addSql('ALTER TABLE sylius_product_variant ADD productGoogleRss_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product_variant ADD CONSTRAINT FK_A29B523570A939A FOREIGN KEY (productGoogleRss_id) REFERENCES app_product_google_rss (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A29B523570A939A ON sylius_product_variant (productGoogleRss_id)');

        $this->addSql(<<<EOM
           UPDATE
               sylius_product_variant variant
               INNER JOIN app_product_google_rss rss
                   on rss.variant_id = variant.id
           SET variant.productGoogleRss_id = rss.id
           WHERE 1
           EOM
        );

        $this->addSql('ALTER TABLE app_product_google_rss DROP FOREIGN KEY FK_C026F3403B69A9AF');
        $this->addSql('DROP INDEX UNIQ_C026F3403B69A9AF ON app_product_google_rss');
        $this->addSql('ALTER TABLE app_product_google_rss DROP variant_id');
    }

    public function down(Schema $schema): void
    {
        // Product part
        $this->addSql('ALTER TABLE app_product_google_rss ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_google_rss ADD CONSTRAINT FK_C026F3404584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C026F3404584665A ON app_product_google_rss (product_id)');

        $this->addSql(<<<EOM
           UPDATE
               app_product_google_rss rss
                   INNER JOIN sylius_product product
                   on product.productGoogleRss_id = rss.id
           SET rss.product_id = product.id
           WHERE 1
          EOM
        );

        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B74570A939A');
        $this->addSql('DROP INDEX UNIQ_677B9B74570A939A ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP productGoogleRss_id');

        // Product variant part
        $this->addSql('ALTER TABLE app_product_google_rss ADD variant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_product_google_rss ADD CONSTRAINT FK_C026F3403B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C026F3403B69A9AF ON app_product_google_rss (variant_id)');

        $this->addSql(<<<EOM
           UPDATE
               app_product_google_rss rss
               INNER JOIN sylius_product_variant variant
                   on variant.productGoogleRss_id = rss.id
           SET rss.variant_id = variant.id
           WHERE 1
           EOM
        );

        $this->addSql('ALTER TABLE sylius_product_variant DROP FOREIGN KEY FK_A29B523570A939A');
        $this->addSql('DROP INDEX UNIQ_A29B523570A939A ON sylius_product_variant');
        $this->addSql('ALTER TABLE sylius_product_variant DROP productGoogleRss_id');
    }
}
