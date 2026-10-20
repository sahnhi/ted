<?php

namespace App\Services\Calendar;

use App\Services\Sentry\ReportingInterface;
use Sentry;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogsCalendarService
{
    public const NO_PROVIDER_ERROR_MSG = 'no_providers';
    public const PRODUCT_DISABLED_ERROR_MSG = 'product_disabled';
    public const NO_SERVICE_ERROR_MSG = 'no_service';
    public const NO_SLOT_AVAILABLE_ERROR_MSG = 'no_slot_available';

    public function __construct(
        private readonly ReportingInterface $reporting,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function logsVariant(string $code): void
    {
        $e = new \Exception('Variant not enabled to check calendar');
        $this->sentryCatch($e, $code, self::PRODUCT_DISABLED_ERROR_MSG, [
            'productCode' => $code,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function logsZones(string $postalCode, string $checkAdvance): never
    {
        $message = match ($checkAdvance) {
            '0' => 'no zone found for postal code ' . $postalCode,
            '1' => 'no members zone found for postal code Zone ' . $postalCode,
            default => null,
        };

        $e = new \Exception($message);
        $this->sentryCatch($e, $postalCode, self::NO_SLOT_AVAILABLE_ERROR_MSG, [
            'zipCode' => $postalCode,
        ]);
    }

    public function logsShippingCategory(string $itemCode): void
    {
        $e = new \Exception('no shipping category for item ' . $itemCode);
        $this->sentryCatch($e, $itemCode, self::NO_SERVICE_ERROR_MSG, [
            'variantCode' => $itemCode,
        ]);
    }

    public function logsVirPlanTransport(string $zipCode): void
    {
        $e = new \Exception('no zipCode Found in Plan Transport Vir ' . $zipCode);
        $this->sentryCatch($e, $zipCode, self::NO_SLOT_AVAILABLE_ERROR_MSG, [
            'zipCode' => $zipCode,
        ]);
    }

    public function sentryCatch(\Exception $e, $parameter, string $messageCode = self::NO_PROVIDER_ERROR_MSG, array $messageParams = []): never
    {
        $this->reporting->reportIssue(
            $e,
            Sentry\Severity::error(),
            ['Calendar' => $parameter]
        );

        throw new UnprocessableEntityHttpException($this->translator->trans(sprintf('app.ui.calendar.errors.%s', $messageCode), $messageParams));
    }
}
