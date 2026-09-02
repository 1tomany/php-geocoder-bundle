<?php

namespace OneToMany\GeocoderBundle;

use OneToMany\Geocoder\Bridge\Google\GoogleProvider;
use OneToMany\Geocoder\Bridge\Mock\MockProvider;
use OneToMany\Geocoder\Bridge\Transport;
use OneToMany\Geocoder\Contract\GeocodingClientInterface;
use OneToMany\Geocoder\GeocodingClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class GeocoderBundle extends AbstractBundle
{
    protected string $extensionAlias = 'onetomany_geocoder';

    private const string GEOCODING_CLIENT_SERVICE = '.onetomany_geocoder.geocoding_client';
    private const string GOOGLE_PROVIDER_SERVICE = '.onetomany_geocoder.provider.google';
    private const string MOCK_PROVIDER_SERVICE = '.onetomany_geocoder.provider.mock';
    private const string PROVIDER_TAG = 'onetomany_geocoder.provider';
    private const string TRANSPORT_SERVICE = '.onetomany_geocoder.transport';

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
                    ->booleanNode('mock')
                        ->defaultFalse()
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
     *   mock: bool,
     * } $config
     */
    #[\Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $services = $container->services();

        $services
            ->set(self::TRANSPORT_SERVICE, Transport::class)
                ->arg('$httpClient', service($config['transport']['http_client']))
                ->arg('$serializer', service('serializer'))

            ->set(self::GEOCODING_CLIENT_SERVICE, GeocodingClient::class)
                ->arg('$providers', tagged_iterator(self::PROVIDER_TAG))
                ->alias(GeocodingClient::class, service(self::GEOCODING_CLIENT_SERVICE))
                ->alias(GeocodingClientInterface::class, service(self::GEOCODING_CLIENT_SERVICE))
        ;

        if (isset($config['google'])) {
            $services
                ->set(self::GOOGLE_PROVIDER_SERVICE, GoogleProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$apiKey', $config['google']['api_key'])
                    ->arg('$apiVersion', $config['google']['api_version'])
                    ->tag(self::PROVIDER_TAG)
            ;
        }

        if ($config['mock']) {
            $services
                ->set(self::MOCK_PROVIDER_SERVICE, MockProvider::class)
                    ->tag(self::PROVIDER_TAG)
            ;
        }
    }
}
