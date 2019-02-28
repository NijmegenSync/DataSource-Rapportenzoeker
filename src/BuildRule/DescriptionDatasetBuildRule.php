<?php

namespace NijmegenSync\DataSource\Rapportenzoeker\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use DCAT_AP_DONL\DCATLiteral;
use NijmegenSync\Dataset\Builder\IDatasetBuildRule;

/**
 * Class DescriptionDatasetBuildRule.
 *
 * Generates a description based on the theme defined in the description field.
 */
class DescriptionDatasetBuildRule implements IDatasetBuildRule
{
    /** @var string */
    private $property;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     */
    public function applyRule(DCATDataset &$dataset, array &$data, array $defaults,
                              array $value_mappers, array $blacklist_mappers,
                              array $whitelist_mappers, array &$notices, string $prefix): void
    {
        if (!isset($data['description'])) {
            $notices[] = \sprintf(
                '%s %s: initial description is absent, abandoning template generation',
                $prefix, $this->property
            );

            return;
        }

        if (null == $data['description'] || '' == \trim($data['description'])) {
            $notices[] = \sprintf(
                '%s %s: initial description is empty, abandoning template generation',
                $prefix, $this->property
            );

            return;
        }

        $template_contents = \file_get_contents(
            \sprintf('%s/../../var/templates/description_template.tpl', __DIR__)
        );
        $generated_description = new DCATLiteral(\sprintf($template_contents, $data['description']));

        if (!$generated_description->validate()->validated()) {
            $notices[] = \sprintf(
                '%s %s: generated description is invalid, discarding',
                $prefix, $this->property
            );

            return;
        }

        $notices[] = \sprintf(
            '%s %s: generated description based on theme',
            $prefix, $this->property
        );

        $dataset->setDescription($generated_description);
    }
}
