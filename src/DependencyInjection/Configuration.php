<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection;

use Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector;
use GuzzleHttp\MessageFormatter;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use GuzzleHttp\Client;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('csa_guzzle');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('profiler')
                    ->canBeEnabled()
                    ->children()
                        ->integerNode('max_body_size')
                            ->info('The maximum size of the body which should be stored in the profiler (in bytes)')
                            ->example('65536')
                            ->defaultValue(GuzzleCollector::MAX_BODY_SIZE)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('logger')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('format')
                            ->beforeNormalization()
                                ->ifInArray(['clf', 'debug', 'short'])
                                ->then(function ($v) {
                                    return constant('GuzzleHttp\MessageFormatter::'.strtoupper($v));
                                })
                            ->end()
                            ->defaultValue(MessageFormatter::CLF)
                        ->end()
                        ->scalarNode('level')
                            ->beforeNormalization()
                                ->ifInArray([
                                    'emergency', 'alert', 'critical', 'error',
                                    'warning', 'notice', 'info', 'debug',
                                ])
                                ->then(function ($v) {
                                    return constant('Psr\Log\LogLevel::'.strtoupper($v));
                                })
                            ->end()
                            ->defaultValue('debug')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_client')->info('The first client defined is used if not set')->end()
                ->booleanNode('autoconfigure')->defaultFalse()->end()
                ->append($this->createCacheNode())
                ->append($this->createClientsNode())
                ->append($this->createMockNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function createCacheNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('cache');
        $node = $treeBuilder->getRootNode();

        $node
            ->canBeEnabled()
            ->validate()
                ->ifTrue(function ($v) {
                    return $v['enabled'] && null === $v['adapter'];
                })
                ->thenInvalid('The \'csa_guzzle.cache.adapter\' key is mandatory if you enable the cache middleware')
            ->end()
            ->children()
                ->scalarNode('adapter')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    private function createClientsNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('clients');
        $node = $treeBuilder->getRootNode();

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('class')->defaultValue(Client::class)->end()
                    ->booleanNode('lazy')->defaultFalse()->end()
                    ->variableNode('config')->end()
                    ->arrayNode('middleware')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('alias')->defaultNull()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createMockNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('mock');
        $node = $treeBuilder->getRootNode();

        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('storage_path')->isRequired()->end()
                ->scalarNode('mode')->defaultValue('replay')->end()
                ->arrayNode('request_headers_blacklist')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('response_headers_blacklist')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
