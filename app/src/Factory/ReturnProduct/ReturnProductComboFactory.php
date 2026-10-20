<?php

namespace App\Factory\ReturnProduct;

use App\Doctrine\Entity\ProductCombo;

class ReturnProductComboFactory implements ReturnFactoryInterface
{
    /**
     * @param ProductCombo $originalCombo
     */
    public function createNewFromExisting($originalCombo): ProductCombo
    {
        $combo = $this->createNew();
        $combo->setQuantity($originalCombo->getQuantity());

        return $combo;
    }

    public function createNew(): ProductCombo
    {
        return new ProductCombo();
    }
}
