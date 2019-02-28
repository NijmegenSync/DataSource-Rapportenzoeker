<?php

namespace NijmegenSync\Test\DataSource\Rapportenzoeker\BuildRule;

use NijmegenSync\DataSource\Rapportenzoeker\BuildRule\BuildRuleAbstractFactory;
use PHPUnit\Framework\TestCase;

class BuildRuleAbstractFactoryTest extends TestCase
{
    public function testExposesDatasetBuildRules(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAllDatasetBuildRules();

        $this->assertTrue(1 == \count($build_rules));
    }

    public function testExposesNoDistributionBuildRules(): void
    {
        $build_rules = BuildRuleAbstractFactory::getAllDistributionBuildRules();

        $this->assertTrue(0 == \count($build_rules));
    }
}
