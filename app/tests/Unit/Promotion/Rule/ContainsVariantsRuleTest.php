<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Rule;

use App\Promotion\Rule\ContainsVariantsRule;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ContainsVariantsRuleTest extends TestCase
{
    private readonly ContainsVariantsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new ContainsVariantsRule();
    }

    public function testItThrowsAnExceptionIfThereAreNoVariants(): void
    {
        // Missing the 'variants' key
        $configuration = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The key "variants" does not exist in the configuration');

        $this->rule->filter($this->createMock(QueryBuilder::class), $configuration);
    }

    public function testItThrowsAnExceptionIfTheConfigurationIsNotAnArray(): void
    {
        $configuration = ['variants' => 'foo'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an array. Got: string');

        $this->rule->filter($this->createMock(QueryBuilder::class), $configuration);
    }

    public function testItCorrectlyBuildsTheQuery(): void
    {
        $configuration = ['variants' => ['VARIANT_CODE_1', 'VARIANT_CODE_2']];

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder
            ->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['o']);

        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('o.code IN (:variant_codes0)')
            ->willReturnSelf();

        $queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('variant_codes0', ['VARIANT_CODE_1', 'VARIANT_CODE_2'])
            ->willReturnSelf();

        $this->rule->filter($queryBuilder, $configuration);
    }
}
