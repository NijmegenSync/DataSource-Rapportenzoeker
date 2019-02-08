<?php

namespace NijmegenSync\DataSource\Rapportenzoeker\Harvester;

use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;

class RapportenzoekerDataSourceHarvester implements IDataSourceHarvester
{
    /** @var string */
    protected $base_uri;

    /**
     * {@inheritdoc}
     */
    public function requiresAuthenticationDetails(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticationDetails(IAuthenticationDetails $details): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        return [];
    }

    /**
     * Setter for the base_uri property.
     *
     * @param string $uri The uri to set
     */
    public function setBaseURI(string $uri): void
    {
        $this->base_uri = $uri;
    }

    /**
     * Getter for the base_uri property, may return null.
     *
     * @return string The base_uri value
     */
    public function getBaseUri(): string
    {
        return $this->base_uri;
    }
}
