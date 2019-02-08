<?php

namespace NijmegenSync\Test\DataSource\Rapportenzoeker;

use NijmegenSync\DataSource\Rapportenzoeker\RapportenzoekerDataSourceManager;
use PHPUnit\Framework\TestCase;

class RapportenzoekerDataSourceManagerTest extends TestCase
{
    public function testIsNotInitializedByDefault(): void
    {
        $manager = new RapportenzoekerDataSourceManager();

        $this->assertFalse($manager->isInitialized());
    }
}
