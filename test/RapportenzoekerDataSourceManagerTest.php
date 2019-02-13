<?php

namespace NijmegenSync\Test\DataSource\Rapportenzoeker;

use NijmegenSync\DataSource\Rapportenzoeker\RapportenzoekerDataSourceManager;
use NijmegenSync\Exception\InitializationException;
use PHPUnit\Framework\TestCase;

class RapportenzoekerDataSourceManagerTest extends TestCase
{
    public function testIsNotInitializedByDefault(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->assertFalse($manager->isInitialized());
    }

    public function testGetNameThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve name, module has not been initialized');

        $manager->getName();
    }

    public function testGetWebAddressThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve web_address, module has not been initialized');

        $manager->getWebAddress();
    }

    public function testGetHarvestingFrequencyThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve harvesting_frequency, module has not been initialized');

        $manager->getHarvestingFrequency();
    }

    public function testGetHarvesterThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve harvester, module has not been initialized');

        $manager->getHarvester();
    }

    public function testGetDefaultsFilePathThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve defaults_file_path, module has not been initialized');

        $manager->getDefaultsFilePath();
    }

    public function testGetValueMappingsFilePathThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve value_mappings_file_path, module has not been initialized');

        $manager->getValueMappingFilePath();
    }

    public function testGetBlacklistMappingFilePathThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve blacklist_mappings_file_path, module has not been initialized');

        $manager->getBlacklistMappingFilePath();
    }

    public function testGetWhitelistMappingFilePathThrowsInitializationExceptionWhenNotInitialized(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('cannot retrieve whitelist_mappings_file_path, module has not been initialized');

        $manager->getWhitelistMappingFilePath();
    }

    public function testInitializeThrowsExceptionWithoutAFileSystemHelper(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->expectException(InitializationException::class);
        $this->expectExceptionMessage('module requires IFileSystemHelper for initialization');

        $manager->initialize();
    }

    public function testNoBuildRulesHaveBeenDefined(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->assertEquals([], $manager->getCustomDatasetBuildRules());
        $this->assertEquals([], $manager->getCustomDistributionBuildRules());
    }
}
