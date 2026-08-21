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

    mock: false
```

The transport uses Symfony's `http_client` service by default, so the `transport` block can normally be omitted. The bundle injects Symfony's `serializer` service into the transport.

The Google block is optional. If it is omitted, Google is not registered with the `OneToMany\Geocoder\GeocoderClient`. The Mock provider is disabled by default and can be enabled in the test environment with the following configuration:

```yaml
# config/packages/test/onetomany_geocoder.yaml
onetomany_geocoder:
    mock: true
```

## Usage

Inject the `OneToMany\Geocoder\Contract\GeocoderClientInterface` client and use its resources:

```php
use OneToMany\Geocoder\Contract\GeocoderClientInterface;
use OneToMany\Geocoder\Resource\Geocode;
use OneToMany\Geocoder\Resource\Reverse;
use OneToMany\Geocoder\Vendor;

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
