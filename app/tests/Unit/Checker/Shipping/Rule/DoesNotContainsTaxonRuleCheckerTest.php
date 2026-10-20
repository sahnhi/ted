<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker\Shipping\Rule;

use App\Checker\Shipping\Rule\ContainsAtLeastTaxonRuleChecker;
use App\Checker\Shipping\Rule\DoesNotContainsTaxonRuleChecker;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\Taxon;

class DoesNotContainsTaxonRuleCheckerTest extends TestCase
{
    private readonly DoesNotContainsTaxonRuleChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new DoesNotContainsTaxonRuleChecker(
            new ContainsAtLeastTaxonRuleChecker()
        );
    }

    public function testItReturnsTrueWhenNoProductsHaveTheRequestedTaxon(): void
    {
        $shipment = $this->createMock(Shipment::class);

        // Product with taxon
        $firstProduct = new Product();
        $firstTaxon = new Taxon();
        $firstTaxon->setCode('NOT_THIS_ONE');

        $firstProductTaxon = new ProductTaxon();
        $firstProductTaxon->setProduct($firstProduct);
        $firstProductTaxon->setTaxon($firstTaxon);
        $firstProduct->addProductTaxon($firstProductTaxon);

        $firstVariant = new ProductVariant();
        $firstVariant->setProduct($firstProduct);

        // Product without taxon
        $secondProduct = new Product();
        $secondTaxon = new Taxon();
        $secondTaxon->setCode('STILL_NOT_THIS_ONE');

        $secondProductTaxon = new ProductTaxon();
        $secondProductTaxon->setProduct($secondProduct);
        $secondProductTaxon->setTaxon($secondTaxon);
        $secondProduct->addProductTaxon($secondProductTaxon);

        $secondVariant = new ProductVariant();
        $secondVariant->setProduct($secondProduct);

        $shipment
            ->expects($this->once())
            ->method('getShippables')
            ->willReturn(new ArrayCollection([$firstVariant, $secondVariant]));

        self::assertTrue($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }

    public function testItReturnsFalseWhenAtLeastOneProductHaveTheRequestedTaxon(): void
    {
        $shipment = $this->createMock(Shipment::class);

        // Product with taxon
        $firstProduct = new Product();
        $firstTaxon = new Taxon();
        $firstTaxon->setCode('THIS_ONE');

        $firstProductTaxon = new ProductTaxon();
        $firstProductTaxon->setProduct($firstProduct);
        $firstProductTaxon->setTaxon($firstTaxon);
        $firstProduct->addProductTaxon($firstProductTaxon);

        $firstVariant = new ProductVariant();
        $firstVariant->setProduct($firstProduct);

        // Product without taxon
        $secondProduct = new Product();
        $secondTaxon = new Taxon();
        $secondTaxon->setCode('NOT_THIS_ONE');

        $secondProductTaxon = new ProductTaxon();
        $secondProductTaxon->setProduct($secondProduct);
        $secondProductTaxon->setTaxon($secondTaxon);
        $secondProduct->addProductTaxon($secondProductTaxon);

        $secondVariant = new ProductVariant();
        $secondVariant->setProduct($secondProduct);

        $shipment
            ->expects($this->once())
            ->method('getShippables')
            ->willReturn(new ArrayCollection([$firstVariant, $secondVariant]));

        self::assertFalse($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }
}
