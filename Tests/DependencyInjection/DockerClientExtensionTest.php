<?php

namespace Docker\ClientBundle\Tests\DependencyInjection;

use Docker\Client\DockerClient;
use Docker\ClientBundle\DependencyInjection\DockerClientExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DockerClientExtensionTest extends TestCase {

    /**
     * @var ContainerBuilder
     */
    private $container;
    /**
     * @var DockerClientExtension
     */
    private $extension;


    public function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new DockerClientExtension();
    }

    public function tearDown(): void
    {
        unset($this->container, $this->extension);
    }

    public function testCreateClients(): void
    {
        $config = [
            'docker_client' => [
                'clients' => [
                    'first_client' => [
                        'remote_socket' => 'unix:///var/run/docker.sock'
                    ],
                    'second_client' => [],
                ]
            ]
        ];

        $this->extension->load($config, $this->container);
        self::assertTrue($this->container->hasAlias('docker_client.client.default'));
        self::assertTrue($this->container->has('docker_client'));
        self::assertTrue($this->container->has('docker_client.client.first_client'));
        self::assertTrue($this->container->has('docker_client.client.second_client'));

        self::assertInstanceOf(DockerClient::class, $this->container->get('docker_client.client.default'));
        self::assertInstanceOf(DockerClient::class, $this->container->get('docker_client.client.first_client'));
        self::assertInstanceOf(DockerClient::class, $this->container->get('docker_client.client.second_client'));

        self::assertNotSame(
            $this->container->get('docker_client.client.first_client'),
            $this->container->get('docker_client.client.second_client')
        );
    }

    public function testClientAlias(): void
    {
        $config = [
            'docker_client' => [
                'clients' => [
                    'first_client' => [
                        'remote_socket' => 'unix:///var/run/docker.sock',
                        'alias' => 'my_docker_client'
                    ],
                ]
            ]
        ];

        $this->extension->load($config, $this->container);
        self::assertTrue($this->container->has(DockerClient::class));
        self::assertTrue($this->container->has('docker_client.client.default'));
        self::assertTrue($this->container->has('docker_client.client.first_client'));
        self::assertTrue($this->container->has('docker_client'));
        self::assertTrue($this->container->has('my_docker_client'));

        self::assertSame(
            $this->container->get('docker_client.client.first_client'),
            $this->container->get('docker_client.client.default')
        );

        self::assertSame(
            $this->container->get(DockerClient::class),
            $this->container->get('docker_client.client.default')
        );

        self::assertSame(
            $this->container->get('docker_client.client.first_client'),
            $this->container->get('docker_client')
        );

        self::assertSame(
            $this->container->get('docker_client.client.first_client'),
            $this->container->get('my_docker_client')
        );
    }
}