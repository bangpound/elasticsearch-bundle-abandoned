<?php

namespace Bangpound\Bundle\ElasticsearchBundle;

/**
 * Deletes and recreates indexes
 */
class Resetter
{
    protected $indexConfigsByName;

    /**
     * Constructor.
     *
     * @param array $indexConfigsByName
     */
    public function __construct(array $indexConfigsByName)
    {
        $this->indexConfigsByName = $indexConfigsByName;
    }

    /**
     * Deletes and recreates all indexes
     */
    public function resetAllIndexes()
    {
        foreach ($this->indexConfigsByName as $name => $indexConfig) {
            $this->resetIndex($name);
        }
    }

    /**
     * Deletes and recreates the named index
     *
     * @param  string                    $indexName
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName)
    {
        $client = new \Elasticsearch\Client();
        $params = array(
            'index' => $this->indexConfigsByName[$indexName]['index_name'],
        );
        if ($client->indices()->exists($params)) {
            $result = $client->indices()->delete($params);
        }
        $result = $client->indices()->create($params);

        foreach ($this->indexConfigsByName[$indexName]['types'] as $type => $settings) {
            $params = array(
                'index' => $this->indexConfigsByName[$indexName]['index_name'],
                'type' => $type,
                'body' => array($type => $settings),
            );
            $result = $client->indices()->putMapping($params);
        }
    }

    /**
     * Deletes and recreates a mapping type for the named index
     *
     * @param  string                    $indexName
     * @param  string                    $typeName
     * @throws \InvalidArgumentException if no index or type mapping exists for the given names
     */
    public function resetIndexType($indexName, $typeName)
    {
        $client = new \Elasticsearch\Client();
        if (!isset($this->indexConfigsByName[$indexName]['types'][$typeName]['properties'])) {
            throw new \InvalidArgumentException(sprintf('The mapping for index "%s" and type "%s" does not exist.', $indexName, $typeName));
        }

        $settings = $this->indexConfigsByName[$indexName]['types'][$typeName];
        $params = array(
            'index' => $this->indexConfigsByName[$indexName]['index_name'],
            'type' => $typeName,
            'body' => array($typeName => $settings),
        );
        $result = $client->indices()->putMapping($params);
    }

    /**
     * create type mapping object
     *
     * @param  array   $indexConfig
     * @return Mapping
     */
    protected function createMapping($indexConfig)
    {
        $mapping = Mapping::create($indexConfig['properties']);

        if (isset($indexConfig['_parent'])) {
            $mapping->setParam('_parent', array('type' => $indexConfig['_parent']['type']));
        }

        return $mapping;
    }

    /**
     * Gets an index config by its name
     *
     * @param string $indexName Index name
     *
     * @param $indexName
     * @return array
     * @throws \InvalidArgumentException if no index config exists for the given name
     */
    protected function getIndexConfig($indexName)
    {
        if (!isset($this->indexConfigsByName[$indexName])) {
            throw new \InvalidArgumentException(sprintf('The configuration for index "%s" does not exist.', $indexName));
        }

        return $this->indexConfigsByName[$indexName];
    }
}
