<?php

namespace NijmegenSync\DataSource\Rapportenzoeker\BuildRule;

use NijmegenSync\Dataset\Builder\IDatasetBuildRule;
use NijmegenSync\Dataset\Builder\IDistributionBuildRule;

/**
 * Class BuildRuleAbstractFactory.
 *
 * Exposes all the custom build rules defined for the Nijmegen rapportenzoeker.
 */
class BuildRuleAbstractFactory
{
    /**
     * Returns all the defined custom build rules for the harvesting of the Nijmegen rapportenzoeker.
     *
     * @return IDatasetBuildRule[] The custom build rules to use
     */
    public static function getAllDatasetBuildRules(): array
    {
        return [
        ];
    }

    /**
     * Returns all the defined custom build rules for the distribution build steps to replace.
     *
     * @return IDistributionBuildRule[] The custom build rules to use
     */
    public static function getAllDistributionBuildRules(): array
    {
        return [];
    }
}
