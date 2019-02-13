<?php

namespace NijmegenSync\Test\DataSource\Rapportenzoeker\Harvesting;

use NijmegenSync\DataSource\Rapportenzoeker\Harvesting\RapportenzoekerDataSourceHarvester;
use PHPUnit\Framework\TestCase;

class RapportenzoekerDataSourceHarvesterTest extends TestCase
{
    public function testRequiresNoAuthenticationDetails(): void
    {
        $harvester = new RapportenzoekerDataSourceHarvester();

        $this->assertFalse($harvester->requiresAuthenticationDetails());
    }
}
