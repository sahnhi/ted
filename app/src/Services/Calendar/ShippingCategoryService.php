<?php

namespace App\Services\Calendar;

use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Repository\Sylius\ShippingCategoryRepository;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Model\ProductReferenceInterface;
use App\Request\CalendarAvailabilityRequest;
use Doctrine\ORM\NonUniqueResultException;

class ShippingCategoryService
{
    private CalendarAvailabilityRequest $request;

    public function getRequest(): CalendarAvailabilityRequest
    {
        return $this->request;
    }

    public function setRequest(CalendarAvailabilityRequest $request): ShippingCategoryService
    {
        $this->request = $request;

        return $this;
    }

    public function __construct(
        private readonly ShippingMethodRepository $shippingMethodRepository,
        private readonly LogsCalendarService $logsCalendarService,
        private readonly ServiceRulesService $serviceRulesService,
        private readonly ShippingCategoryRepository $shippingCategoryRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function check(array $shippingMethods): ?array
    {
        $resultShippingMethod = $shippingMethodCodes = $serviceRulesId = $codeArray = [];

        foreach ($shippingMethods as $method) {
            if (!empty($method['isShippingMethod']) && 1 === $method['isShippingMethod']) {
                $shippingMethodCodes[] = $method['code'];
            } elseif (!empty($method['service_rules_id'])) {
                $serviceRulesId[] = $method['service_rules_id'];
            }
        }

        // We exclude Assembly And Retrieval items
        $filteredProducts = array_filter($this->request->getProducts(), static fn (array $item): bool => !(in_array($item['code'], ProductReferenceInterface::REPOSSESS_REFS, true)
            || in_array($item['code'], ProductReferenceInterface::ASSEMBLY_CODES, true)));

        foreach (array_values($filteredProducts) as $key => $item) {
            $tmp = [];

            $shippingCategory = $this->shippingCategoryRepository->findShippingCategoryByVariant($item['code']);
            $shippingMethods = $this->shippingMethodRepository->getByShippingCode($item['code'], $shippingMethodCodes);

            /**
             * @var ServiceRules $serviceRulesShippingCategoryAvailable
             */
            $serviceRulesShippingCategoryAvailable = $this->serviceRulesService->hasServiceShippingCategory($shippingCategory, $serviceRulesId);

            $shippingMethods = $this->serviceRulesService->checkShippingPriorityService($shippingMethods, $serviceRulesShippingCategoryAvailable);

            if (empty($shippingMethods)) {
                $this->logsCalendarService->logsShippingCategory($item['code']);
            }

            foreach ($shippingMethods as $priority => $shippingMethod) {
                if (0 === $key) {
                    $codeArray[$priority] = $shippingMethod['code'];
                } else {
                    $tmp[$priority] = $shippingMethod['code'];
                }

                $resultShippingMethod[$priority] = $shippingMethod;
            }

            if (0 < $key) {
                $codeArray = array_intersect_assoc($codeArray, $tmp);
                foreach ($resultShippingMethod as $priority => $shippingMethod) {
                    if (!array_key_exists($priority, $codeArray)) {
                        unset($resultShippingMethod[$priority]);
                    }
                }
            }
        }

        return $this->removeDuplicateShippingMethod($resultShippingMethod);
    }

    private function removeDuplicateShippingMethod(array $resultShippingMethod): array
    {
        $keysWithSameValue = [];

        foreach ($resultShippingMethod as $priority => $shippingMethodCode) {
            $keysWithSameValue[$shippingMethodCode['code']][] = $priority;
        }

        $prioritiesToRemove = [];
        foreach ($keysWithSameValue as $priority) {
            if (count($priority) > 1) {
                $largestPriority = max($priority);
                $prioritiesToRemove[] = $largestPriority;
            }
        }

        // Use array_filter to remove the elements with the largest keys
        return array_filter($resultShippingMethod, fn ($key): bool => !in_array($key, $prioritiesToRemove), ARRAY_FILTER_USE_KEY);
    }
}
