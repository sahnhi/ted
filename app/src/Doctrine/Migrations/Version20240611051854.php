<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\FtpConfigurationName;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611051854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update ftp_configuration_name for Warehouse and ShippingMethod';
    }

    public function up(Schema $schema): void
    {
        // Warehouse
        $this->addSql(<<<SQL
            UPDATE erp_warehouse SET ftp_configuration_name = :ftpConfigurationName WHERE ftp_details_id = 1
        SQL, ['ftpConfigurationName' => FtpConfigurationName::SPIDY->value]);
        $this->addSql(<<<SQL
            UPDATE erp_warehouse SET ftp_configuration_name = :ftpConfigurationName WHERE ftp_details_id = 2
        SQL, ['ftpConfigurationName' => FtpConfigurationName::VIR->value]);

        // Shipping Method
        $this->addSql(<<<SQL
            UPDATE sylius_shipping_method SET ftp_configuration_name = :ftpConfigurationName WHERE ftp_details_id = 1
        SQL, ['ftpConfigurationName' => FtpConfigurationName::SPIDY->value]);
        $this->addSql(<<<SQL
            UPDATE sylius_shipping_method SET ftp_configuration_name = :ftpConfigurationName WHERE ftp_details_id = 2
        SQL, ['ftpConfigurationName' => FtpConfigurationName::VIR->value]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE erp_warehouse SET ftp_configuration_name = null WHERE ftp_configuration_name IS NOT NULL');
        $this->addSql('UPDATE sylius_shipping_method SET ftp_configuration_name = null WHERE ftp_configuration_name IS NOT NULL');
    }
}
