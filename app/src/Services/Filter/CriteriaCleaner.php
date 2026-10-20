<?php

namespace App\Services\Filter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CriteriaCleaner implements CriteriaCleanerInterface
{
    private const FILTER_KEY_TYPE = 'type';

    private readonly Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function clean(): array
    {
        $criteria = $this->request->get('criteria');

        if (!is_array($criteria)) {
            return [];
        }

        foreach ($criteria as $key => $criterion) {
            if (!$this->recursivelyHasValue($criterion)) {
                unset($criteria[$key]);
            }
        }

        return $criteria;
    }

    private function recursivelyHasValue(array|string|null $criterion): bool
    {
        if (is_array($criterion)) {
            foreach ($criterion as $childKey => $child) {
                if (self::FILTER_KEY_TYPE === $childKey) {
                    continue;
                }

                if ($this->recursivelyHasValue($child)) {
                    return true;
                }
            }

            return false;
        }

        return !empty($criterion);
    }
}
