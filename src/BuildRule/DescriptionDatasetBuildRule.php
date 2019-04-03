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
        if (!isset($data[$this->property])) {
            $notices[] = \sprintf(
                '%s %s: initial %s is absent, abandoning template generation',
                $prefix, $this->property, $this->property
            );

            return;
        }

        if (null == $data[$this->property] || '' == \trim($data[$this->property])) {
            $notices[] = \sprintf(
                '%s %s: initial %s is empty, abandoning template generation',
                $prefix, $this->property, $this->property
            );

            return;
        }

        $template_contents = \file_get_contents(
            \sprintf('%s/../../var/templates/%s_template.tpl', __DIR__, $this->property)
        );
        $generated_description = new DCATLiteral(\sprintf($template_contents, $data[$this->property]));
        $validation_result     = $generated_description->validate();

        if (!$validation_result->validated()) {
            foreach ($validation_result->getMessages() as $message) {
                $notices[] = \sprintf('%s %s: %s', $prefix, $this->property, $message);
            }

            $notices[] = \sprintf(
                '%s %s: generated %s is invalid, discarding',
                $prefix, $this->property, $this->property
            );

            return;
        }

        $notices[] = \sprintf(
            '%s %s: generated %s based on theme',
            $prefix, $this->property, $this->property
        );

        $dataset->setDescription($generated_description);
    }
}
