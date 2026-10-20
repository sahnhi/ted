<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109132120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Duplicate review for products';
    }

    public function up(Schema $schema): void
    {
        // bed-frame
        $this->addSql('CREATE TEMPORARY TABLE temp_table AS SELECT * FROM sylius_product_review WHERE product_id = ?', [18]);
        $this->addSql('UPDATE temp_table SET product_id = ?, order_id = NULL, shipping_method_id = NULL', [279]);
        $this->addSql('INSERT INTO sylius_product_review (product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star) SELECT product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star FROM temp_table');
        $this->addSql('DROP TEMPORARY TABLE temp_table');

        // kid-steel-bed-frame
        $this->addSql('CREATE TEMPORARY TABLE temp_table AS SELECT * FROM sylius_product_review WHERE product_id = ?', [324]);
        $this->addSql('UPDATE temp_table SET product_id = ?, order_id = NULL, shipping_method_id = NULL', [327]);
        $this->addSql('INSERT INTO sylius_product_review (product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star) SELECT product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star FROM temp_table');
        $this->addSql('DROP TEMPORARY TABLE temp_table');

        // storage-bed-frame
        $this->addSql('CREATE TEMPORARY TABLE temp_table AS SELECT * FROM sylius_product_review WHERE product_id = ?', [86]);
        $this->addSql('UPDATE temp_table SET product_id = ?, order_id = NULL, shipping_method_id = NULL', [282]);
        $this->addSql('INSERT INTO sylius_product_review (product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star) SELECT product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star FROM temp_table');
        $this->addSql('DROP TEMPORARY TABLE temp_table');

        // electric-bed-frame
        $this->addSql('CREATE TEMPORARY TABLE temp_table AS SELECT * FROM sylius_product_review WHERE product_id = ?', [241]);
        $this->addSql('UPDATE temp_table SET product_id = ?, order_id = NULL, shipping_method_id = NULL', [286]);
        $this->addSql('INSERT INTO sylius_product_review (product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star) SELECT product_id, author_id, title, rating, comment, status, created_at, updated_at, shipping_method_id, order_id, locale, remercie, remercie_date, star FROM temp_table');
        $this->addSql('DROP TEMPORARY TABLE temp_table');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM sylius_product_review WHERE product_id = ?', [279]);
        $this->addSql('DELETE FROM sylius_product_review WHERE product_id = ?', [327]);
        $this->addSql('DELETE FROM sylius_product_review WHERE product_id = ?', [282]);
        $this->addSql('DELETE FROM sylius_product_review WHERE product_id = ?', [286]);
    }
}
