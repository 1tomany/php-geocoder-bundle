<?php

namespace OneToMany\Bundle\GeocoderBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

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
                    ->arrayNode('gemini')
                        ->children()
                            ->stringNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1beta')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->children()
                            ->stringNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1')
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
     *   gemini?: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *   },
     *   openai?: array{
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

        $services
            ->set(self::TRANSPORT_SERVICE, Transport::class)
                ->arg('$httpClient', service($config['transport']['http_client']))
                ->arg('$serializer', service('serializer'))

            ->set(self::FILES_SERVICE, Files::class)
                ->arg('$providers', tagged_iterator(self::FILE_PROVIDER_TAG))
                ->alias(Files::class, service(self::FILES_SERVICE))
                ->alias(FilesInterface::class, service(self::FILES_SERVICE))

            ->set(self::QUERIES_SERVICE, Queries::class)
                ->arg('$providers', tagged_iterator(self::QUERY_PROVIDER_TAG))
                ->alias(Queries::class, service(self::QUERIES_SERVICE))
                ->alias(QueriesInterface::class, service(self::QUERIES_SERVICE))

            ->set(self::AI_CLIENT_SERVICE, AiClient::class)
                ->arg('$files', service(self::FILES_SERVICE))
                ->arg('$queries', service(self::QUERIES_SERVICE))
                ->alias(AiClientInterface::class, service(self::AI_CLIENT_SERVICE))
        ;

        if (isset($config['gemini'])) {
            $services
                ->set(self::GEMINI_NORMALIZER_SERVICE, GeminiQueryNormalizer::class)
                    ->tag('serializer.normalizer')

                ->set(self::GEMINI_FILE_PROVIDER_SERVICE, GeminiFileProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                    ->tag(self::FILE_PROVIDER_TAG)

                ->set(self::GEMINI_QUERY_PROVIDER_SERVICE, GeminiQueryProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                    ->tag(self::QUERY_PROVIDER_TAG)
            ;
        }

        if (isset($config['openai'])) {
            $services
                ->set(self::OPENAI_NORMALIZER_SERVICE, OpenAIQueryNormalizer::class)
                    ->tag('serializer.normalizer')

                ->set(self::OPENAI_FILE_PROVIDER_SERVICE, OpenAIFileProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::FILE_PROVIDER_TAG)

                ->set(self::OPENAI_QUERY_PROVIDER_SERVICE, OpenAIQueryProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::QUERY_PROVIDER_TAG)
            ;
        }
    }
}
