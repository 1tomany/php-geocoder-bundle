# PHP Geocoder Bundle for Symfony

This package wraps the [`1tomany/php-geocoder`](https://github.com/1tomany/php-geocoder) library into an easy to use Symfony bundle.

## Installation

Install the bundle using Composer:

```console
composer require 1tomany/php-geocoder-bundle
```

## Configuration

To change the default configuration, create a file named `onetomany_geocoder.yaml` in `config/packages/` with the following contents and adjust accordingly:

```yaml
onetomany_geocoder:
    transport:
        http_client: http_client

    google:
        api_key: "%env(GOOGLE_API_KEY)%"
        api_version: v4
```

The transport uses Symfony's `http_client` service by default, so the `transport` block can normally be omitted. The bundle registers its provider-specific query normalizers with Symfony's `serializer` service and injects that serializer into the transport.

Provider blocks are optional. If a provider block is omitted, that provider is not registered with the `OneToMany\Geocoder\GeocoderClient` facade.

## Usage

Inject the `OneToMany\Geocoder\Contract\GeocoderClientInterface` facade and use its resources:

```php
use OneToMany\Geocoder\Contract\GeocoderClientInterface;
use OneToMany\Geocoder\Vendor;

use function sprintf;

final readonly class GeocodeAddress
{
    public function __construct(
        private GeocoderClientInterface $geocoderClient,
    ) {
    }

    public function __invoke(string $path): void
    {
        $response = $this->geocoderClient->geocode(
            Vendor::Google,
            new Geocode(
                street: '123 Main Street',
                unit: null,
                city: 'Dallas',
                zip: '75205',
                state: 'TX',
                country: null,
            ),
        );

        $response = $this->geocoderClient->reverse(
            Vendor::Google,
            new Reverse(
                latitude: 32.10391494,
                longitude: -96.3931030,
            ),
        );
    }
}
```

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
