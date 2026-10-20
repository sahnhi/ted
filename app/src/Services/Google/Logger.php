<?php

namespace App\Services\Google;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\PsrHandler;
use Psr\Log\LoggerInterface;

class Logger extends PsrHandler
{
    protected string $logName;
    protected LoggingClient $client;

    /** @var LoggerInterface[] */
    protected array $loggers;

    public function __construct(
        string $googleCredentials,
        string $logName,
        bool $bubble = true
    ) {
        $this->logName = 'sylius-' . $logName;
        $this->client = new LoggingClient([
            'keyFilePath' => $googleCredentials,
        ]);
        $this->bubble = $bubble;
        $this->loggers = [];
    }

    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $this->getLogger($record['channel'])->log(strtolower((string) $record['level_name']), $record['message'], $record['context']);

        return false === $this->bubble;
    }

    protected function getLogger($channel): LoggerInterface
    {
        if (!isset($this->loggers[$channel])) {
            $this->loggers[$channel] = $this->client->psrLogger($this->logName, ['labels' => ['context' => $channel]]);
        }

        return $this->loggers[$channel];
    }
}
