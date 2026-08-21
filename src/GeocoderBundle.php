<?php

namespace OneToMany\Bundle\GeocoderBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GeocoderBundle extends AbstractBundle
{
    protected string $extensionAlias = 'onetomany_geocoder';

    /**
     * @see Symfony\Component\Config\Definition\ConfigurableInterface
     *
     * @param DefinitionConfigurator<'array'> $definition
     */
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('transport')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('google')
                        ->children()
                            ->stringNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v4')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface
     *
     * @param array{
     *   transport: array{
     *     http_client: non-empty-string,
     *   },
     *   google?: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *   },
     * } $config
     */
    #[\Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $services = $container->services();
    }
}
