<?php

namespace NijmegenSync\DataSource\Rapportenzoeker;

use NijmegenSync\Contracts\BaseNijmegenSyncModule;
use NijmegenSync\DataSource\Harvesting\HarvestingFrequency;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use NijmegenSync\DataSource\IDataSourceManager;
use NijmegenSync\DataSource\Rapportenzoeker\BuildRule\BuildRuleAbstractFactory;
use NijmegenSync\DataSource\Rapportenzoeker\Harvester\RapportenzoekerDataSourceHarvester;
use NijmegenSync\Exception\InitializationException;
use NijmegenSync\Exception\IOException;

/**
 * Class RapportenzoekerDataSourceManager.
 *
 * Manager for the module responsible for harvesting the potential datasets from the Nijmegen
 * rapportenzoeker.
 *
 * For most of its settings the manager relies on the `var/settings.json` file.
 */
class RapportenzoekerDataSourceManager extends BaseNijmegenSyncModule implements IDataSourceManager
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $web_address;

    /** @var string */
    protected $base_uri;

    /** @var string */
    protected $harvesting_frequency;

    /** @var string */
    protected $defaults_file_path;

    /** @var string */
    protected $value_mappings_file_path;

    /** @var string */
    protected $blacklist_mappings_file_path;

    /** @var string */
    protected $whitelist_mappings_file_path;

    /** @var RapportenzoekerDataSourceHarvester */
    protected $harvester;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->harvester = null;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if ($this->isInitialized()) {
            throw new InitializationException('module is already initialized');
        }

        if (null == $this->file_system_helper) {
            throw new InitializationException('module requires IFileSystemHelper for initialization');
        }

        try {
            $settings_file     = \sprintf('%s/%s', __DIR__, '../var/settings.json');
            $settings_contents = $this->file_system_helper->readFile($settings_file);
            $settings_json     = \json_decode($settings_contents, true);
            $settings_keys     = ['name', 'web_address', 'harvesting_frequency', 'base_uri'];

            foreach ($settings_keys as $key) {
                if (!\array_key_exists($key, $settings_json)) {
                    throw new InitializationException(
                        \sprintf('settings file is missing key %s', $key)
                    );
                }

                $this->$key = $settings_json[$key];
            }

            $settings_file_keys = [
                'defaults_file_path', 'value_mappings_file_path', 'blacklist_mappings_file_path',
                'whitelist_mappings_file_path',
            ];

            foreach ($settings_file_keys as $key) {
                if (!\array_key_exists($key, $settings_json)) {
                    throw new InitializationException(
                        \sprintf('settings file is missing key %s', $key)
                    );
                }

                $this->$key = \sprintf('%s/%s/%s', __DIR__, '../var', $settings_json[$key]);
            }

            if (!HarvestingFrequency::isValid($this->harvesting_frequency)) {
                throw new InitializationException(
                    \sprintf('module declared illegal harvesting frequency %s', $this->harvesting_frequency)
                );
            }

            $this->harvester = new RapportenzoekerDataSourceHarvester();
            $this->harvester->setBaseURI($this->base_uri);

            $this->is_initialized = true;
        } catch (IOException $e) {
            throw new InitializationException();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getName(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve name, module has not been initialized'
            );
        }

        return $this->name;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getWebAddress(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve web_address, module has not been initialized'
            );
        }

        return $this->web_address;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getHarvestingFrequency(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve harvesting_frequency, module has not been initialized'
            );
        }

        return $this->harvesting_frequency;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getHarvester(): IDataSourceHarvester
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve harvester, module has not been initialized'
            );
        }

        return $this->harvester;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getDefaultsFilePath(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve defaults_file_path, module has not been initialized'
            );
        }

        return $this->defaults_file_path;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getValueMappingFilePath(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve value_mappings_file_path, module has not been initialized'
            );
        }

        return $this->value_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getBlacklistMappingFilePath(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve blacklist_mappings_file_path, module has not been initialized'
            );
        }

        return $this->blacklist_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InitializationException Thrown if the module has not been initialized yet
     */
    public function getWhitelistMappingFilePath(): string
    {
        if (!$this->is_initialized) {
            throw new InitializationException(
                'cannot retrieve whitelist_mappings_file_path, module has not been initialized'
            );
        }

        return $this->whitelist_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomDatasetBuildRules(): array
    {
        return BuildRuleAbstractFactory::getAllDatasetBuildRules();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomDistributionBuildRules(): array
    {
        return BuildRuleAbstractFactory::getAllDistributionBuildRules();
    }
}
