<?php

namespace NijmegenSync\Test\DataSource\Rapportenzoeker\Harvester;

use NijmegenSync\DataSource\Rapportenzoeker\Harvester\RapportenzoekerDataSourceHarvester;
use PHPUnit\Framework\TestCase;

class RapportenzoekerDataSourceHarvesterTest extends TestCase
{
    public function testRequiresNoAuthenticationDetails(): void
    {
        $harvester = new RapportenzoekerDataSourceHarvester();

        $this->assertFalse($harvester->requiresAuthenticationDetails());
    }
}
