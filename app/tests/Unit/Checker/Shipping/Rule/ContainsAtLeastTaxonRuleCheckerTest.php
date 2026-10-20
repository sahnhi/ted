<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker\Shipping\Rule;

use App\Checker\Shipping\Rule\ContainsAtLeastTaxonRuleChecker;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\Taxon;

class ContainsAtLeastTaxonRuleCheckerTest extends TestCase
{
    private readonly ContainsAtLeastTaxonRuleChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ContainsAtLeastTaxonRuleChecker();
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheConfigurationDoesNotContainsTheRequiredData(): void
    {
        // Missing the 'taxon' key
        $configuration = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration is missing the taxon code');

        $this->checker->isEligible(new Shipment(), $configuration);
    }

    public function testItReturnsFalseWhenAllTheProductsDoesNotHaveTheRequiredTaxon(): void
    {
        $shipment = $this->createMock(Shipment::class);

        // Product with taxon
        $productWithoutTaxon = new Product();
        $invalidTaxon = new Taxon();
        $invalidTaxon->setCode('STILL_NOT_THIS_ONE');

        $invalidProductTaxon = new ProductTaxon();
        $invalidProductTaxon->setProduct($productWithoutTaxon);
        $invalidProductTaxon->setTaxon($invalidTaxon);
        $productWithoutTaxon->addProductTaxon($invalidProductTaxon);

        $variantWithoutTaxon = new ProductVariant();
        $variantWithoutTaxon->setProduct($productWithoutTaxon);

        // Product without taxon
        $productAlsoWithoutTaxon = new Product();
        $alsoInvalidTaxon = new Taxon();
        $alsoInvalidTaxon->setCode('NOT_THIS_ONE');

        $alsoInvalidProductTaxon = new ProductTaxon();
        $alsoInvalidProductTaxon->setProduct($productAlsoWithoutTaxon);
        $alsoInvalidProductTaxon->setTaxon($alsoInvalidTaxon);
        $productAlsoWithoutTaxon->addProductTaxon($alsoInvalidProductTaxon);

        $variantAlsoWithoutTaxon = new ProductVariant();
        $variantAlsoWithoutTaxon->setProduct($productAlsoWithoutTaxon);

        $shipment
            ->expects($this->once())
            ->method('getShippables')
            ->willReturn(new ArrayCollection([$variantWithoutTaxon, $variantAlsoWithoutTaxon]));

        self::assertFalse($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }

    public function testItReturnsTrueWhenAtLeastOneProductDoesHaveTheRequiredTaxon(): void
    {
        $shipment = $this->createMock(Shipment::class);

        // Product with taxon
        $productWithTaxon = new Product();
        $validTaxon = new Taxon();
        $validTaxon->setCode('THIS_ONE');

        $validProductTaxon = new ProductTaxon();
        $validProductTaxon->setProduct($productWithTaxon);
        $validProductTaxon->setTaxon($validTaxon);
        $productWithTaxon->addProductTaxon($validProductTaxon);

        $variantWithTaxon = new ProductVariant();
        $variantWithTaxon->setProduct($productWithTaxon);

        // Product without taxon
        $productWithoutTaxon = new Product();
        $invalidTaxon = new Taxon();
        $invalidTaxon->setCode('NOT_THIS_ONE');

        $invalidProductTaxon = new ProductTaxon();
        $invalidProductTaxon->setProduct($productWithoutTaxon);
        $invalidProductTaxon->setTaxon($invalidTaxon);
        $productWithoutTaxon->addProductTaxon($invalidProductTaxon);

        $variantWithoutTaxon = new ProductVariant();
        $variantWithoutTaxon->setProduct($productWithoutTaxon);

        $shipment
            ->expects($this->once())
            ->method('getShippables')
            ->willReturn(new ArrayCollection([$variantWithTaxon, $variantWithoutTaxon]));

        self::assertTrue($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }
}
