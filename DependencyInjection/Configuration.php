<?php

namespace Bangpound\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    private $supportedDrivers = array('orm', 'mongodb', 'propel');

    private $configArray = array();

    public function __construct($configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bangpound_elasticsearch', 'array');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->scalarNode('default_index')->end()
                ->arrayNode('serializer')
                    ->treatNullLike(array())
                    ->children()
                        ->scalarNode('callback_class')->defaultValue('FOS\ElasticaBundle\Serializer\Callback')->end()
                        ->scalarNode('serializer')->defaultValue('serializer')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
    {
        return $this->getConfigTreeBuilder()->buildTree();
    }

    /**
     * Adds the configuration for the "clients" key
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->beforeNormalization()
                            ->ifTrue(function ($v) { return isset($v['host']) && isset($v['port']); })
                            ->then(function ($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'host' => $v['host'],
                                            'port' => $v['port'],
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function ($v) { return isset($v['url']); })
                            ->then(function ($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'url' => $v['url'],
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('url')->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')->end()
                            ->scalarNode('headers')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the configuration for the "indexes" key
     */
    private function addIndexesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('index')
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('index_name')->end()
                            ->scalarNode('client')->end()
                            ->variableNode('types')->end()
                        ->end()
                ->end()
            ->end()
        ;
    }
}
