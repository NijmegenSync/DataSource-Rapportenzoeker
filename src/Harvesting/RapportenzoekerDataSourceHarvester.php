<?php

namespace NijmegenSync\DataSource\Rapportenzoeker\Harvesting;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\Dataset\Mapping\ValueMapper;
use NijmegenSync\DataSource\Harvesting\DataSourceUnavailableHarvestingException;
use NijmegenSync\DataSource\Harvesting\HarvestResult;
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

    /** @var string */
    protected $resource_base_uri;

    /** @var ValueMapper */
    protected $pre_builder_theme_mapper;

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
     * Setter for the resource_base_uri property.
     *
     * @param string $uri The uri to set
     */
    public function setResourceBaseURI(string $uri): void
    {
        $this->resource_base_uri = $uri;
    }

    /**
     * Setter for the pre_builder_theme_mapper property.
     *
     * @param ValueMapper $mapper The ValueMapper to use
     */
    public function setPreBuilderThemeMapper(ValueMapper $mapper): void
    {
        $this->pre_builder_theme_mapper = $mapper;
    }

    /**
     * Getter for the base_uri property, may return null.
     *
     * @return null|string The base_uri value
     */
    public function getBaseUri(): ?string
    {
        return $this->base_uri;
    }

    /**
     * Getter for the resourcebase_uri property, may return null.
     *
     * @return null|string The resource_base_uri value
     */
    public function getResourceBaseUri(): ?string
    {
        return $this->resource_base_uri;
    }

    /**
     * Getter for the pre_builder_theme_mapper property, may return null.
     *
     * @return null|ValueMapper The assigned ValueMapper
     */
    public function getPreBuilderThemeMapper(): ?ValueMapper
    {
        return $this->pre_builder_theme_mapper;
    }

    /**
     * Extracts metadata of datasets from the given XML stream.
     *
     * @param StreamInterface $response_body The stream containing the harvested data
     *
     * @return array The extracted datasets
     */
    private function extractDatasets(StreamInterface $response_body): array
    {
        $datasets            = [];
        $parsable_response   = new \DOMDocument();
        @$parsable_response->loadHTML($response_body);
        $response_as_html    = new \DOMXPath($parsable_response);
        $harvestable_objects = $response_as_html->query('//div[@id="content"]/table/tr[@class="result" or @class="result ai"]');
        $datasets_per_theme  = [];

        foreach ($harvestable_objects as $harvestable_object) {
            $theme = $response_as_html->query('td[@class="thema"]', $harvestable_object);

            if (0 == \count($theme)) {
                continue;
            }

            if (empty($theme->item(0)->nodeValue)) {
                continue;
            }

            $transformed_theme = $this->pre_builder_theme_mapper->map($theme->item(0)->nodeValue);

            $datasets_per_theme[$transformed_theme][] = $harvestable_object;
        }

        foreach ($datasets_per_theme as $dataset_theme => $raw_dataset) {
            $dataset                = [];
            $dataset['identifier']  = \sprintf('https://opendata.nijmegen.nl/dataset/rapportenzoeker-%s', $this->sluggify($dataset_theme));
            $dataset['title']       = $dataset_theme;
            $dataset['theme']       = [$dataset_theme];
            $dataset['description'] = $dataset_theme;

            foreach ($raw_dataset as $potential_resource) {
                $access_url   = $response_as_html->query('td[@class="icon"]/a/@href', $potential_resource);
                $title        = $response_as_html->query('td[@class="title"]/strong[@class="name"]/text()', $potential_resource);
                $description  = $response_as_html->query('td[@class="title"]/text()', $potential_resource);
                $type         = $response_as_html->query('td[@class="icon"]/a/img/@alt', $potential_resource);
                $modified     = $response_as_html->query('td[@class="jaar"]', $potential_resource);

                if (\count($modified) > 0) {
                    $modified_value = (int) $modified->item(0)->nodeValue;

                    if (!\array_key_exists('modificationDate', $dataset) || $dataset['modificationDate'] < $modified_value) {
                        $dataset['modificationDate'] = $modified_value;
                    }
                }

                $resource = [];

                if (\count($access_url) > 0) {
                    $resource['accessURL'] = \sprintf(
                        '%s%s',
                        $this->resource_base_uri,
                        \str_replace(' ', '%20', $access_url->item(0)->nodeValue)
                    );

                    try {
                        $response         = (new Client([]))->request('HEAD', $resource['accessURL']);
                        $resource['size'] = $response->getHeaderLine('Content-Length');
                    } catch (GuzzleException $e) {
                        continue;
                    }
                }

                if (\count($title) > 0) {
                    $resource['title'] = $title->item(0)->nodeValue;
                }

                if (\count($description) > 0) {
                    $resource['description'] = $description->item(0)->nodeValue;
                }

                if (\count($type) > 0) {
                    $resource['format']    = $type->item(0)->nodeValue;
                    $resource['mediaType'] = $type->item(0)->nodeValue;
                }

                $dataset['resources'][] = $resource;
            }

            $dataset['modificationDate'] = \sprintf('%s-12-31T00:00:00', $dataset['modificationDate']);

            $harvest_result = new HarvestResult();
            $harvest_result->setResult($dataset);

            $datasets[] = $harvest_result;
        }

        return $datasets;
    }

    /**
     * Sluggifies a given string, meaning it is transformed to lower case and all special characters
     * are replaced with '-'.
     *
     * @param string $input The string to sluggify
     *
     * @return string The sluggified string
     */
    private function sluggify(string $input): string
    {
        $pattern     = '/[^A-Za-z0-9-]/';
        $replacement = '-';

        return \strtolower(\preg_replace($pattern, $replacement, $input));
    }
}
