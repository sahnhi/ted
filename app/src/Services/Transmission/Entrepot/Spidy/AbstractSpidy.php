<?php

namespace App\Services\Transmission\Entrepot\Spidy;

use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Model\FtpConfiguration;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Filesystem\GoogleStorageTransmissionFilesystem;
use App\Services\Sentry\ReportingInterface;
use FTP\Connection;
use phpseclib3\Net\SFTP;
use Sentry;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractSpidy
{
    public const SPIDY_PREFIX = 'CR_EDI';
    public const VIR_PREFIX = 'EVTLIV';
    public const SPIDY_POD_COL = 5; // Column "POD" number in Spidy File
    public const SPIDY_POD_PROOF_COL = 12; // Actual "POD" column number in Spidy File
    public const VIR_POD_COL = 8; // Column "POD" and "LIV" number in Vir file
    public const VIR_CFM_COL = 9; // Column "CFM" and "BL" number in Vir file
    public const VIR_POD_PROOF_COL = 10; // Actual "POD" column number in Vir File
    public const SPIDY_ORDER_NUMBER = 4; // Column of OrderNumber in Spidy File
    public const VIR_ORDER_NUMBER = 2; // Column of OrderNumber in Vir File
    public const SPIDY_VIR_POD = 'POD';
    public const SPIDY_VIR_LIV = 'LIV';
    public const VIR_CFM = 'CFM';
    public const VIR_BL = 'BL';
    public const VIR_LIV_ECH = ['LIV', 'ECH'];
    public const FR_EXPRESS_SHORT_EXP_COL = 5; // Column "EXP" number in Spidy File
    public const FR_EXPRESS_EXP_COL = 6; // Columne "EXPEDIE" number in Spidy File
    public const FR_EXPRESS_EXP = 'EXP';
    public const FR_EXPRESS_EXPEDIE = 'EXPEDIE';
    public const SPIDY_TRACKING_COL = 12;
    public const DELIVERED_FOLDER = 'delivered/';
    public const CANCEL_FOLDER = 'cancel/';

    /** @var string */
    protected $projectDir;

    /** @var string privateFolder */
    protected $privateFolder;

    /**
     * GetPackingAction constructor.
     */
    public function __construct(
        string $projectDir,
        protected readonly FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        protected readonly ExportCsv $exportCsv,
        protected readonly GoogleStorageTransmissionFilesystem $googleStorageTransmissionFilesystem,
        private readonly ReportingInterface $reporting
    ) {
        $this->privateFolder = $projectDir . '/src/Resources/private/';
        $this->projectDir = $projectDir;
    }

    /**
     * @return array{0: SFTP|Connection|bool, 1: FtpConfiguration}
     *
     * @throws FtpConfigurationNotFoundException
     */
    public function connect(FtpConfigurationName $ftpName, bool $sftp = true): array
    {
        $ftpConfiguration = $this->ftpConfigurationRegistry->getFtpConfigurationByName($ftpName);

        $ftp_server = $ftpConfiguration->host;
        $ftp_name = $ftpConfiguration->user;
        $ftp_pass = $ftpConfiguration->password;

        $resource = $sftp ?
            $this->sftp($ftp_server, $ftp_name, $ftp_pass) :
            $this->ftp($ftp_server, $ftp_name, $ftp_pass);

        return [$resource, $ftpConfiguration];
    }

    private function sftp(string $ftp_server, string $ftp_name, string $ftp_pass): SFTP
    {
        $sftp = new SFTP($ftp_server);
        $sftp->login($ftp_name, $ftp_pass);

        return $sftp;
    }

    private function ftp(string $ftp_server, string $ftp_name, string $ftp_pass): bool|Connection
    {
        $connect = ftp_connect($ftp_server);
        ftp_login($connect, $ftp_name, $ftp_pass);
        ftp_pasv($connect, true);

        return $connect;
    }

    public function mkdir(string $dirPath, int $mode = 0777): bool
    {
        return is_dir($dirPath) || mkdir($dirPath, $mode, true);
    }

    public function removeFiles($resource, $ftpFile, $localFile, $folder, bool $sftp = true)
    {
        // Start by making a backup
        $this->sendToGoogle($folder, $localFile);

        $sftp ? $resource->delete($ftpFile) : ftp_delete($resource, $ftpFile);
        unlink($localFile);
    }

    public function put(SFTP $sftp, $remoteFile, $localFile, $error, $number)
    {
        $response = $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE);

        if (!$response) {
            $this->reporting->reportIssue(
                $error,
                Sentry\Severity::error(),
                ['cancel_transmission_ftp' => $number],
                ['payload' => $remoteFile]
            );
        }
    }

    public function backupPayload(string $payload, string $googleFolder, string $orderNumber, string $prefix)
    {
        $monthFolderPath = date('Y/') . date('F');
        $path = $this->privateFolder . $googleFolder . $prefix . '/' . $monthFolderPath;

        $fileName = date('YmdHis') . '_' . $orderNumber . '.txt';
        $filePath = $path . '/' . $prefix . '_' . $fileName;

        try {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists($path)) {
                $fileSystem->mkdir($path);
            }
            $fileSystem->touch($filePath);
            $fileSystem->chmod($filePath, 0777);
            $fileSystem->appendToFile($filePath, $payload);

            $this->sendToGoogle($googleFolder, $filePath);
            $fileSystem->remove($filePath);
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['register_payload' => $orderNumber],
                [
                    'payload' => $payload,
                    'path' => $filePath,
                ]
            );
        }
    }

    public function sendToGoogle(string $folder, string $localFile): void
    {
        $split = explode($folder, $localFile);
        $googleFile = ucfirst($folder) . end($split);

        $this->googleStorageTransmissionFilesystem->upload($localFile, $googleFile);
    }
}
