<?php

declare(strict_types=1);

namespace App\Controller\Admin\Action;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ███████████████████████████████████████████████████████████████████████████████
 * █ WARNING: This is a one-time use script for importing product texts          █
 * █ Code quality and maintainability are not priorities as this will be        █
 * █ used only once for data migration. Use with caution.                       █
 * █                                                                            █
 * █ Handles CSV/ZIP imports for:                                               █
 * █ - Product texts                                                            █
 * █ - Satellite pages                                                          █
 * ███████████████████████████████████████████████████████████████████████████████.
 */
final class ImportProductTexts extends AbstractController
{
    /**
     * Mapping between import types and their corresponding command names.
     */
    private const TYPE_TO_COMMAND = [
        'product' => 'app:import-products-v2',
        'satellite_pages' => 'app:import-satellite-pages',
    ];
    private const RENDER_TEMPLATE = '@SyliusAdmin/Product/Temporary/importfile.html.twig';
    private const ALLOWED_MIME_TYPES = ['text/csv', 'application/zip'];

    private ?string $tempDir = null;

    /**
     * @param KernelInterface $kernel Used to execute import commands
     * @param LoggerInterface $logger For logging import process and errors
     */
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Main entry point for file imports.
     * Handles both GET (displays form) and POST (processes import) requests.
     *
     * @Route("/admin/file_importer/upload", name="file_importer_upload", methods={"GET", "POST"})
     */
    public function __invoke(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render(self::RENDER_TEMPLATE, ['content' => '']);
        }

        try {
            $type = $this->validateType($request->get('type'));
            $file = $this->validateFile($request->files->get('file'));

            $files = $this->extractFiles($file->getRealPath());
            $this->processFiles($files, self::TYPE_TO_COMMAND[$type]);
            $this->cleanupTempDirectory();

            return $this->redirectToRoute('file_importer_upload');
        } catch (\Exception $e) {
            return $this->render(self::RENDER_TEMPLATE, [
                'content' => $e->getMessage() . ' ' . $e->getLine(),
            ]);
        }
    }

    /**
     * Validates the import type from the request.
     * Ensures the type exists in the TYPE_TO_COMMAND mapping.
     *
     * @throws \InvalidArgumentException if type is invalid or missing
     */
    private function validateType(?string $type): string
    {
        if (!$type || !isset(self::TYPE_TO_COMMAND[$type])) {
            throw new \InvalidArgumentException('Invalid import type provided');
        }

        return $type;
    }

    /**
     * Validates the uploaded file.
     * Checks if:
     * - File was actually uploaded
     * - File is of correct type (CSV or ZIP).
     *
     * @throws \InvalidArgumentException if file validation fails
     */
    private function validateFile($file): UploadedFile
    {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('No file uploaded');
        }

        if (!in_array($file->getClientMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Invalid file type. Only CSV and ZIP files are allowed');
        }

        return $file;
    }

    /**
     * Extracts files from the uploaded file.
     * If ZIP file, extracts all CSV files from it.
     * If CSV file, returns it directly.
     *
     * @param string $filePath Path to the uploaded file
     *
     * @return array List of file paths to process
     *
     * @throws \RuntimeException if ZIP extraction fails or no CSV files found
     */
    private function extractFiles(string $filePath): array
    {
        if ('application/zip' === mime_content_type($filePath)) {
            $zip = new \ZipArchive();
            if (true !== $zip->open($filePath)) {
                throw new \RuntimeException('Failed to open ZIP file');
            }

            $this->tempDir = sys_get_temp_dir() . '/import_' . uniqid();
            mkdir($this->tempDir);
            $zip->extractTo($this->tempDir);
            $zip->close();

            $files = glob($this->tempDir . '/*.csv');
            if (empty($files)) {
                throw new \RuntimeException('No CSV files found in ZIP archive');
            }

            return $files;
        }

        return [$filePath];
    }

    /**
     * Processes the extracted files using the appropriate import command.
     * Executes the command for each file and logs the results.
     *
     * @param array  $files   List of file paths to process
     * @param string $command Command name to execute for each file
     *
     * @throws \RuntimeException if command execution fails
     */
    private function processFiles(array $files, string $command): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        foreach ($files as $filePath) {
            $this->logger->info('Processing file: ' . $filePath);

            try {
                $input = new ArrayInput([
                    'command' => $command,
                    'filepath' => $filePath,
                ]);

                $result = $application->run($input, new NullOutput());

                if (0 !== $result) {
                    $this->logger->error('Command failed for file: ' . $filePath);
                    throw new \RuntimeException("Command failed for file: $filePath");
                }

                $this->logger->info('Successfully processed file: ' . $filePath);
            } catch (\Exception $e) {
                $this->logger->error('Error processing file: ' . $filePath, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Cleans up temporary directory if it exists.
     */
    private function cleanupTempDirectory(): void
    {
        if (null !== $this->tempDir && is_dir($this->tempDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($this->tempDir);
        }
    }
}
