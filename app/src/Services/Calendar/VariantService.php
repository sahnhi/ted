<?php

namespace App\Services\Calendar;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\ProductComboRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Exception\ProductVariantNotFound;
use App\Request\CalendarAvailabilityRequest;
use Doctrine\ORM\NonUniqueResultException;

class VariantService
{
    private CalendarAvailabilityRequest $request;

    public function getRequest(): CalendarAvailabilityRequest
    {
        return $this->request;
    }

    public function setRequest(CalendarAvailabilityRequest $request): VariantService
    {
        $this->request = $request;

        return $this;
    }

    public function __construct(
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly LogsCalendarService $logsCalendarService,
        private readonly ProductComboRepositoryInterface $productComboRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function check(): array
    {
        $checkVariantData = [];
        $nextAvailability = new \DateTime('now');
        $weighting = 0;

        foreach ($this->request->getProducts() as $item) {
            $productWeighting = $this->getProductWeighting($item['code']);

            $nextAvailability = $this->request->isWithoutStock() ?
                $nextAvailability :
                $this->checkLogisticVariants($item['code'], $nextAvailability);

            if (null !== $productWeighting) {
                $nextAvailability = $this->request->isWithoutStock() ?
                    $nextAvailability :
                    $this->getLastAvailability($nextAvailability, $item['code']);
                $weighting += $item['quantity'] * $productWeighting;
                $checkVariantData = [
                    'nextAvailability' => $nextAvailability,
                    'weighting' => $weighting,
                ];
            } else {
                $this->logsCalendarService->logsVariant($item['code']);
            }
        }

        return $checkVariantData;
    }

    private function getProductWeighting($code): ?float
    {
        $weighting = null;
        $variant = $this->productVariantRepository->findOneByCode($code);

        if (!$variant instanceof ProductVariant) {
            throw new ProductVariantNotFound($code);
        }

        // get weighting here
        if ($variant->isEnabled()) {
            $weighting = $variant->getProduct()->getWeighting();
        }

        return $weighting;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getLastAvailability(\DateTimeInterface $lastAvailability, string $code, ?\DateTimeInterface $day = null): \DateTimeInterface
    {
        $nextAvailability = $this->productVariantRepository
            ->getFirstAvailableShippingDate($code, $day ?? $this->request->getNow());

        return max($lastAvailability, $nextAvailability);
    }

    /**
     * @throws \Exception
     */
    public function checkLogisticVariants(string $mainCode, \DateTimeInterface $nextAvailability, ?\DateTimeInterface $day = null): \DateTimeInterface // TODO: Think about warehouse condition implementation
    {
        /** @var ProductVariant|null $variant */
        $variant = $this->productVariantRepository->findOneBy(['code' => $mainCode]);
        if (null !== $variant) {
            $combos = $this->productComboRepository->findByVariantCode($mainCode);
            foreach ($combos as $combo) {
                if (null !== $combo->getProductVariantLogistic()) {
                    $code = $combo->getProductVariantLogistic()->getCode();

                    $nextAvailability = $this->getLastAvailability($nextAvailability, $code, $day);

                    $variants = explode('_', (string) $code);
                    foreach ($variants as $code) {
                        $nextAvailability = $this->getAvailabilityFromCombo($code, $nextAvailability, $day);
                    }
                }
            }
        }

        $variants = explode('_', $mainCode);
        foreach ($variants as $code) {
            $nextAvailability = $this->getAvailabilityFromCombo($code, $nextAvailability, $day);
        }

        return $nextAvailability;
    }

    /**
     * @throws \Exception
     */
    private function getAvailabilityFromCombo(string $code, \DateTimeInterface $nextAvailability, ?\DateTimeInterface $day = null): \DateTimeInterface
    {
        $combos = $this->productComboRepository->findByVariantCode($code);

        foreach ($combos as $combo) {
            $nextAvailability = $this->getLastAvailability($nextAvailability, $combo->getProductVariant()->getCode(), $day);
            if (null !== $combo->getProductVariantLogistic() && $combo->getQuantity() >= 1) {
                $nextAvailability = $this->getLastAvailability($nextAvailability, $combo->getProductVariantLogistic()->getCode(), $day);
            }
        }

        return $nextAvailability;
    }
}
