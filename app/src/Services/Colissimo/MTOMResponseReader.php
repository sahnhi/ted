<?php

namespace App\Services\Colissimo;

class MTOMResponseReader
{
    public const CONTENT_TYPE = 'Content-Type: application/json;';
    public const UUID = '/--uuid:/'; // This is the separator of each part of the response
    public const CONTENT = 'Content-';
    public $attachments = [];
    public $soapResponse = [];
    public $uuid;

    /**
     * @throws \Exception
     */
    public function __construct($response)
    {
        if (str_contains((string) $response, self::CONTENT_TYPE)) {
            $this->parseResponse($response);
        } else {
            throw new \Exception(sprintf('This response is not : %s', self::CONTENT_TYPE));
        }
    }

    private function parseResponse($response)
    {
        $content = [];
        $matches = [];
        preg_match_all(self::UUID, (string) $response, $matches, PREG_OFFSET_CAPTURE);
        for ($i = 0; $i < count($matches[0]) - 1; ++$i) {
            if ($i + 1 < count($matches[0])) {
                $content[$i] = substr((string) $response, $matches[0][$i][1], $matches[0][$i + 1][1] - $matches[0][$i][1]);
            } else {
                $content[$i] = substr((string) $response, $matches[0][$i][1], strlen((string) $response));
            }
        }
        foreach ($content as $part) {
            if (null === $this->uuid) {
                $uuidStart = 0;
                $uuidEnd = 0;
                $uuidStart = strpos($part, self::UUID, 0) + strlen(self::UUID);
                $uuidEnd = strpos($part, "\r\n", $uuidStart);
                $this->uuid = substr($part, $uuidStart, $uuidEnd - $uuidStart);
            }
            $header = $this->extractHeader($part);
            if (count($header) > 0) {
                if (str_contains((string) $header['Content-Type'], 'type="text/xml"')) {
                    $this->soapResponse['header'] = $header;
                    $this->soapResponse['data'] = trim(substr($part, $header['offsetEnd']));
                } else {
                    $attachment['header'] = $header;
                    $attachment['data'] = trim(substr($part, $header['offsetEnd']));
                    $this->attachments[] = $attachment;
                }
            }
        }
    }

    /**
     * Exclude the header from the Web Service response.
     *
     * @param string $part
     *
     * @return array $header
     */
    private function extractHeader($part): array
    {
        $header = [];
        $headerLineStart = strpos($part, self::CONTENT, 0);
        $endLine = 0;
        while (false !== $headerLineStart) {
            $header['offsetStart'] = $headerLineStart;
            $endLine = strpos($part, "\r\n", $headerLineStart);
            $headerLine = explode(': ', substr($part, $headerLineStart, $endLine - $headerLineStart));
            $header[$headerLine[0]] = $headerLine[1];
            $headerLineStart = strpos($part, self::CONTENT, $endLine);
        }
        $header['offsetEnd'] = $endLine;

        return $header;
    }
}
