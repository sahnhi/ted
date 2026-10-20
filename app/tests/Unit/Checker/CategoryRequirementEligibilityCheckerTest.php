<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker;

use App\Checker\CategoryRequirementEligibilityChecker;
use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Shipping\Model\ShippableInterface;
use Sylius\Component\Shipping\Model\ShippingMethodInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class CategoryRequirementEligibilityCheckerTest extends TestCase
{
    private readonly CategoryRequirementEligibilityChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new CategoryRequirementEligibilityChecker();
    }

    public function testItApprovesCategoryRequirementIfCategoriesMatch(): void
    {
        $shippingCategory = $this->createMock(ShippingCategory::class);
        $shippingCategory2 = $this->createMock(ShippingCategory::class);

        $shippingMethod = $this->createMock(ShippingMethod::class);
        $shippingMethod->method('getShippingCategory')->willReturn(new ArrayCollection([$shippingCategory]));
        $shippingMethod->method('getCategoryRequirement')->willReturnOnConsecutiveCalls(
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ANY,
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ALL,
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_NONE
        );

        $shippable = $this->createMock(ShippableInterface::class);
        $shippable->method('getShippingCategory')->willReturnOnConsecutiveCalls(
            $shippingCategory,
            $shippingCategory,
            $shippingCategory2
        ); // first two times we will return the same shippingCategory. But we need different for the third test to pass

        $subject = $this->createMock(ShippingSubjectInterface::class);
        $subject->method('getShippables')->willReturn(new ArrayCollection([$shippable]));

        self::assertTrue($this->checker->isEligible($subject, $shippingMethod));
        self::assertTrue($this->checker->isEligible($subject, $shippingMethod));
        self::assertTrue($this->checker->isEligible($subject, $shippingMethod));
    }

    public function testItApprovesCategoryRequirementIfNoCategoryIsRequired(): void
    {
        $shippingMethod = $this->createMock(ShippingMethod::class);
        $shippingMethod->method('getShippingCategory')->willReturn(new ArrayCollection([]));

        self::assertTrue($this->checker->isEligible($this->createMock(ShippingSubjectInterface::class), $shippingMethod));
    }

    public function testItDeniesCategoryRequirementIfCategoriesDoNotMatch(): void
    {
        $shippingCategory = $this->createMock(ShippingCategory::class);
        $shippingCategory2 = $this->createMock(ShippingCategory::class);

        $shippingMethod = $this->createMock(ShippingMethod::class);
        $shippingMethod->method('getShippingCategory')->willReturnOnConsecutiveCalls(
            new ArrayCollection([$shippingCategory]),
            new ArrayCollection([$shippingCategory]),
            new ArrayCollection([$shippingCategory2])
        );

        $shippingMethod->method('getCategoryRequirement')->willReturnOnConsecutiveCalls(
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ANY,
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ALL,
            ShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_NONE
        );

        $shippable = $this->createMock(ShippableInterface::class);
        $shippable->method('getShippingCategory')->willReturn($shippingCategory2);

        $subject = $this->createMock(ShippingSubjectInterface::class);
        $subject->method('getShippables')->willReturn(new ArrayCollection([$shippable]));

        self::assertFalse($this->checker->isEligible($subject, $shippingMethod));
        self::assertFalse($this->checker->isEligible($subject, $shippingMethod));
        self::assertFalse($this->checker->isEligible($subject, $shippingMethod));
    }
}
