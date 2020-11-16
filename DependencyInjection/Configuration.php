<?php


namespace Docker\ClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('docker_client');

        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('docker_client');
        }

        $rootNode->children()
            ->arrayNode("clients")
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('remote_socket')
                        ->defaultValue('unix:///var/run/docker.sock')
                    ->end()
                    ->scalarNode('alias')->defaultValue(null)->end()
                    ->arrayNode('registries')
                        ->arrayPrototype('array')
                            ->children()
                                ->scalarNode('username')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}