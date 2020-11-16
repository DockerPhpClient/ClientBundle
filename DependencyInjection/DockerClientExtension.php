<?php


namespace Docker\ClientBundle\DependencyInjection;

use Docker\Client\DockerClient;
use Docker\Client\DockerClientFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DockerClientExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->addClients($config['clients'], $container);
    }

    /**
     * @param array $clients
     * @param ContainerBuilder $container
     */
    private function addClients(array $clients, ContainerBuilder $container): void
    {
        foreach ($clients as $name => $client) {
            $this->createClient(
                $name,
                $client['remote_socket'],
                $client['alias'],
                $client['registries'],
                $container
            );
        }

        reset($clients);
        $this->setDefaultClient(key($clients), $container);
    }

    /**
     * @param $name
     * @param ContainerBuilder $container
     */
    private function setDefaultClient($name, ContainerBuilder $container): void
    {
        $container->setAlias('docker_client.client.default', sprintf('docker_client.client.%s', $name));
        $container->setAlias(DockerClient::class, 'docker_client.client.default');
    }

    /**
     * @param $name
     * @param $remote_socket
     * @param $alias
     * @param array $registries
     * @param ContainerBuilder $container
     */
    private function createClient($name, $remote_socket, $alias, array $registries, ContainerBuilder $container): void
    {
        $definition = new Definition('%docker_client.client.class%');
        $definition->addArgument(['remote_socket' => $remote_socket]);
        $definition->addArgument(['options' => ['registries' => $registries]]);
        $definition->setFactory(array(DockerClientFactory::class, 'create'));

        // Add Service to Container
        $container->setDefinition(
            sprintf('docker_client.client.%s', $name),
            $definition
        );

        // If alias option is set, create a new alias
        if (null !== $alias) {
            $container->setAlias($alias, sprintf('docker_client.client.%s', $name));
        }
    }
}