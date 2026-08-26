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

The transport uses Symfony's `http_client` service by default, so the `transport` block can normally be omitted. The bundle injects Symfony's default `serializer` service into the transport.

The `google` block is optional. If it is omitted, the Google provider is not registered with the `GeocoderClient`. The Mock provider is disabled by default and can be enabled in the test environment with the following configuration:

```yaml
when@test:
    onetomany_geocoder:
        mock: true
```

## Usage

Inject the `OneToMany\Geocoder\Contract\GeocoderClientInterface` object and use its resources:

```php
<?php

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

    public function __invoke(): void
    {
        $response = $this->geocoderClient->geocode(
            Vendor::Google,
            new Geocode('123', 'Main Street', null, 'Dallas', '75205', 'TX', 'US'),
        );

        $response = $this->geocoderClient->reverse(
            Vendor::Google,
            new Reverse('32.10391494', '-96.3931030'),
        );
    }
}
```

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
