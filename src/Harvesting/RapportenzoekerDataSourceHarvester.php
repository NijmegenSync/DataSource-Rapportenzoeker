<?php

namespace NijmegenSync\DataSource\Rapportenzoeker\Harvesting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use Psr\Http\Message\StreamInterface;

/**
 * Class RapportenzoekerDataSourceHarvester.
 *
 * Performs the harvesting operation on the Nijmegen Rapportenzoeker webapplication. It requires no
 * authentication details to perform this task.
 */
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
        // Rapportenzoeker harvester requires no AuthenticationDetails, so we ignore any that are
        // given.
    }

    /**
     * {@inheritdoc}
     */
    public function harvest(): array
    {
        $client  = new Client(['base_uri' => $this->base_uri]);

        try {
            $request = $client->request(
                'GET',
                '/rapportenzoeker/lijst/'
            );

            if (200 !== $request->getStatusCode()) {
                throw new DataSourceUnavailableHarvestingException(
                    \sprintf(
                        'datasource responded with HTTP statuscode %s',
                        $request->getStatusCode()
                    )
                );
            }

            return $this->extractDatasets($request->getBody());
        } catch (GuzzleException $e) {
            throw new DataSourceUnavailableHarvestingException($e->getMessage());
        }
    }

    private function extractDatasets(StreamInterface $response_body): array
    {
        $datasets          = [];
        $parsable_response = new \DOMDocument();
        @$parsable_response->loadHTML($response_body);
        $response_as_html    = new \DOMXPath($parsable_response);
        $harvestable_objects = $response_as_html->query('//div[@id="content"]/table/tr[@class="result" or @class="result ai"]');

        foreach ($harvestable_objects as $harvestable_object) {
            /** @var $harvestable_object \DOMNode */
            $identifier  = $response_as_html->query('td[@class="icon"]/a/@href', $harvestable_object);
            $title       = $response_as_html->query('td[@class="title"]/strong[@class="name"]/text()', $harvestable_object);
            $description = $response_as_html->query('td[@class="title"]/text()', $harvestable_object);
            $keywords    = $response_as_html->query('td[@class="title"]/strong[@class="name"]/@title', $harvestable_object);
            $theme       = $response_as_html->query('td[@class="thema"]', $harvestable_object);
            $modified    = $response_as_html->query('td[@class="jaar"]', $harvestable_object);
            $type        = $response_as_html->query('td[@class="icon"]/a/img/@alt', $harvestable_object);

            $dataset  = [];
            $resource = [];

            if (\count($identifier) > 0) {
                $dataset['identifier'] = \sprintf('%s%s', $this->base_uri, \str_replace(' ', '%20', $identifier->item(0)->nodeValue));
                $resource['accessURL'] = \sprintf('%s%s', $this->base_uri, \str_replace(' ', '%20', $identifier->item(0)->nodeValue));
                $resource['title']     = \basename($resource['accessURL']);

                $client = new Client([]);

                try {
                    $response = $client->request('HEAD', $resource['accessURL']);
                    $resource['size'] = $response->getHeaderLine('Content-Length');
                } catch (GuzzleException $e) {
                    continue;
                }
            }

            if (\count($title) > 0) {
                $dataset['title'] = $title->item(0)->nodeValue;
            }

            if (\count($description) > 0) {
                $dataset['description'] = $description->item(0)->nodeValue;
            }

            if (\count($keywords) > 0) {
                $dataset['keyword'] = $keywords->item(0)->nodeValue;
            }

            if (\count($theme) > 0) {
                $dataset['theme'] = $theme->item(0)->nodeValue;
            }

            if (\count($modified) > 0) {
                $dataset['modified'] = \sprintf('%s-12-31', $modified->item(0)->nodeValue);
            }

            if (\count($type) > 0) {
                $resource['description'] = $type->item(0)->nodeValue;
                $resource['format'] = $type->item(0)->nodeValue;
                $resource['mediaType'] = $type->item(0)->nodeValue;
            }

            $dataset['resources'][] = $resource;
            $datasets[]             = $dataset;
        }

        return $datasets;
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
