<?php

namespace App\Services\Admin\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCsv
{
    public function generateCsv($data = null, $filename = 'export'): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($data): void {
            $this->createCsv('php://output', $data);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

    public function createCsv($filename, $data = null, $removeQuotes = false, $bom = true): void
    {
        if ('php://output' !== $filename) {
            $dirname = dirname((string) $filename);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0755, true);
            }
        }

        $handle = fopen($filename, 'wb+');

        foreach ($data as $row) {
            foreach ($row as $k => $r) {
                if (is_array($r)) {
                    if ($bom) {
                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    }
                    $removeQuotes ? fwrite($handle, implode(';', $r) . "\r\n") : fputcsv($handle, $r, ';');
                    unset($row[$k]);
                }
            }

            if ($bom) {
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            }
            $removeQuotes ? fwrite($handle, implode(';', $row) . "\r\n") : fputcsv($handle, $row, ';');
        }

        fclose($handle);
    }

    public function parseResource($resources = null, $i = 1)
    {
        $maxResults = 10;
        $firstResult = $maxResults * $i;

        $resources->getData()->getAdapter()->getQuery()->setFirstResult($firstResult);
        $resources->getData()->getAdapter()->getQuery()->setMaxResults($maxResults);

        return $resources->getData()->getAdapter()->getQuery();
    }

    public function readCsv($filename, $delimiter = ','): array
    {
        $rows = [];
        if (($handle = fopen($filename, 'rb')) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }

    public function contentFormatCSV($data = null): mixed
    {
        $content = '';
        foreach ($data as $row) {
            foreach ($row as $k => $r) {
                if (is_array($r)) {
                    $content .= chr(0xEF) . chr(0xBB) . chr(0xBF);
                    $content .= implode(';', $r) . "\r\n";
                    unset($row[$k]);
                }
            }
            $content .= chr(0xEF) . chr(0xBB) . chr(0xBF);
            $content .= implode(';', $row) . "\r\n";
        }

        return $content;
    }
}
