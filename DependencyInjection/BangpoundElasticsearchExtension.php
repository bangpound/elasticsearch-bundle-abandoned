<?php

namespace Bangpound\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use InvalidArgumentException;

class BangpoundElasticsearchExtension extends Extension
{
    protected $indexConfigs     = array();

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (empty($config['clients']) || empty($config['indexes'])) {
            throw new InvalidArgumentException('You must define at least one client and one index');
        }

        if (empty($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        if (empty($config['default_index'])) {
            $keys = array_keys($config['indexes']);
            $config['default_index'] = reset($keys);
        }

        $clientIdsByName = $this->loadClients($config['clients'], $container);
        $serializerConfig = isset($config['serializer']) ? $config['serializer'] : null;
        $indexIdsByName  = $this->loadIndexes($config['indexes'], $container, $clientIdsByName, $config['default_client'], $serializerConfig);
        $indexRefsByName = array_map(function ($id) {
            return new Reference($id);
        }, $indexIdsByName);

        $this->loadResetter($this->indexConfigs, $container);

//        $container->setAlias('bangpound_elasticsearch.client', sprintf('bangpound_elasticsearch.client.%s', $config['default_client']));
//        $container->setAlias('bangpound_elasticsearch.index', sprintf('bangpound_elasticsearch.index.%s', $config['default_index']));
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($config);
    }

    /**
     * Loads the configured clients.
     *
     * @param  array            $clients   An array of clients configurations
     * @param  ContainerBuilder $container A ContainerBuilder instance
     * @return array
     */
    protected function loadClients(array $clients, ContainerBuilder $container)
    {
        $clientIds = array();
        foreach ($clients as $name => $clientConfig) {
            $clientDef = $container->getDefinition('bangpound_elasticsearch.client');
            $clientDef->replaceArgument(0, $clientConfig);

            $clientId = sprintf('bangpound_elasticsearch.client.%s', $name);

            $container->setDefinition($clientId, $clientDef);

            $clientIds[$name] = $clientId;
        }

        return $clientIds;
    }

    /**
     * Loads the configured indexes.
     *
     * @param array            $indexes         An array of indexes configurations
     * @param ContainerBuilder $container       A ContainerBuilder instance
     * @param array            $clientIdsByName
     * @param $defaultClientName
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function loadIndexes(array $indexes, ContainerBuilder $container, array $clientIdsByName, $defaultClientName, $serializerConfig)
    {
        $indexIds = array();
        foreach ($indexes as $name => $index) {
            if (isset($index['client'])) {
                $clientName = $index['client'];
                if (!isset($clientIdsByName[$clientName])) {
                    throw new InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
                }
            } else {
                $clientName = $defaultClientName;
            }

            $clientId = $clientIdsByName[$clientName];
            $this->indexConfigs[$name] = $index;
        }

        return $indexIds;
    }

    /**
     * Loads the resetter
     *
     * @param array                                                   $indexConfigs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function loadResetter(array $indexConfigs, ContainerBuilder $container)
    {
        $resetterDef = $container->getDefinition('bangpound_elasticsearch.resetter');
        $resetterDef->replaceArgument(0, $indexConfigs);
    }
}
