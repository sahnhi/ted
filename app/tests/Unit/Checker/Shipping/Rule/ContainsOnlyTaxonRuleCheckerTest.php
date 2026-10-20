<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker\Shipping\Rule;

use App\Checker\Shipping\Rule\ContainsOnlyTaxonRuleChecker;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductTaxon;
use Sylius\Component\Core\Model\Taxon;

class ContainsOnlyTaxonRuleCheckerTest extends TestCase
{
    private readonly ContainsOnlyTaxonRuleChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ContainsOnlyTaxonRuleChecker();
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheConfigurationDoesNotContainsTheRequiredData(): void
    {
        // Missing the 'taxon' key
        $configuration = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration is missing the taxon code');

        $this->checker->isEligible(new Shipment(), $configuration);
    }

    public function testItReturnsFalseWhenAtLeastOneProductDoesNotHaveTheRequiredTaxon(): void
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

        self::assertFalse($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }

    public function testItReturnsTrueWhenAllTheProductsHaveTheRequiredTaxon(): void
    {
        $shipment = $this->createMock(Shipment::class);

        $taxon = new Taxon();
        $taxon->setCode('THIS_ONE');

        $firstProduct = new Product();

        $firstProductTaxon = new ProductTaxon();
        $firstProductTaxon->setProduct($firstProduct);
        $firstProductTaxon->setTaxon($taxon);
        $firstProduct->addProductTaxon($firstProductTaxon);

        $firstVariant = new ProductVariant();
        $firstVariant->setProduct($firstProduct);

        $secondProduct = new Product();

        $secondProductTaxon = new ProductTaxon();
        $secondProductTaxon->setProduct($secondProduct);
        $secondProductTaxon->setTaxon($taxon);
        $secondProduct->addProductTaxon($secondProductTaxon);

        $secondVariant = new ProductVariant();
        $secondVariant->setProduct($secondProduct);

        $shipment
            ->expects($this->once())
            ->method('getShippables')
            ->willReturn(new ArrayCollection([$firstVariant, $secondVariant]));

        self::assertTrue($this->checker->isEligible($shipment, ['taxon' => 'THIS_ONE']));
    }
}
