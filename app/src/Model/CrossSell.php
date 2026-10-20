<?php

namespace App\Model;

class CrossSell
{
    /**
     *  0 => 'Matelas seul',
     *  1 => 'Au moins un matelas adulte + sommier',
     *  2 => 'Au moins un matelas adulte + autre produit',
     *  3 => 'Au moins un sommier',
     *  4 => 'Autres',.
     */
    public const MATTRESS_ONLY = 0;
    public const MATTRESS_AND_BED_BASE = 1;
    public const MATTRESS_WITHOUT_BED_BASE = 2;
    public const BED_BASE = 3;
    public const NO_MATTRESS_OR_BED_BASE = 4;

    private $hasMattress = false;
    private $hasBedBase = false;
    private $noMattress = false;
    private $noBedBase = false;

    /**
     * @return int
     */
    public function getCrossSell($items)
    {
        $nbItems = count($items);

        foreach ($items as $item) {
            $product = $item->getProduct();

            foreach ($product->getTaxons() as $taxon) {
                if (1 === $nbItems) {
                    if ('mattress' === $taxon->getCode()) {
                        return self::MATTRESS_ONLY;
                    } elseif ('bed-frame' === $taxon->getCode()) {
                        return self::BED_BASE;
                    } else {
                        return self::NO_MATTRESS_OR_BED_BASE;
                    }
                } else {
                    if ('mattress' === $taxon->getCode()) {
                        $this->hasMattress = true;
                    } else {
                        $this->noMattress = true;
                    }

                    if ('bed-frame' === $taxon->getCode()) {
                        $this->hasBedBase = true;
                    } else {
                        $this->noBedBase = true;
                    }
                }
            }
        }

        if ($this->hasBedBase && $this->hasMattress) {
            return self::MATTRESS_AND_BED_BASE;
        } elseif ($this->hasMattress && $this->noBedBase) {
            return self::MATTRESS_WITHOUT_BED_BASE;
        } elseif ($this->noMattress && $this->hasBedBase) {
            return self::BED_BASE;
        } else {
            return self::NO_MATTRESS_OR_BED_BASE;
        }
    }
}
