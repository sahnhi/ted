<?php

namespace App\Services\Transmission;

use App\Doctrine\Entity\OrderItemProductCombo;
use App\Doctrine\Repository\ProductComboRepository;
use Doctrine\ORM\NonUniqueResultException;

class OrderItemComboProductService
{
    public function __construct(
        private readonly ProductComboRepository $productComboRepository
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function addItemProductCombo($item, $variantCode)
    {
        $variantCombos = $this->productComboRepository->findByVariantCode($variantCode);

        if (!empty($variantCombos)) {
            foreach ($variantCombos as $variantCombo) {
                $orderItemProductCombo = new OrderItemProductCombo();
                $orderItemProductCombo->setProductCombo($variantCombo);
                $orderItemProductCombo->setOrderItem($item);
                $item->addOrderItemProductCombo($orderItemProductCombo);
            }
        }

        return $variantCombos;
    }
}
