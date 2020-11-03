# php-openapi-faker

![Tests](https://github.com/canvural/php-openapi-faker/workflows/Tests/badge.svg)
[![codecov](https://codecov.io/gh/canvural/php-openapi-faker/branch/master/graph/badge.svg)](https://codecov.io/gh/canvural/php-openapi-faker)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/canvural/php-openapi-faker/master)](https://infection.github.io)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-brightgreen.svg?style=flat&logo=php)](https://phpstan.org)

Library to generate fake data for your OpenAPI requests, responses and schemas.

```php
$faker = \Vural\OpenAPIFaker\OpenAPIFaker::createFromJson($yourSchemaAsJson);
$fakeData = $faker->mockResponse('/todos','GET');
```

## Installation

You can install the package via composer:

```bash
composer require --dev canvural/php-openapi-faker
```

## Usage

First you need to create an instance of `OpenAPIFaker` with your schema that you want to fake data from.
```php
$faker = \Vural\OpenAPIFaker\OpenAPIFaker::createFromJson($yourSchemaAsJson);
```

Then you can use `mockResponse`, `mockRequest` and `mockSchema` methods on it to generate fake data for your requests, responses and schemas. Like so:

```php
$fakeData = $faker->mockResponse('/todos','GET');
```

### Options

There are some options you can use to modify some behaviour. You can pass options as an associative array to `setOptions` method in `OpenAPIFaker`. For example:

```php
$faker = \Vural\OpenAPIFaker\OpenAPIFaker::createFromJson($yourSchemaAsJson)
    ->setOptions(['minItems' => 5]);
```

Below you can find explanation for each option.

#### `minItems`
Overrides `minItems` property if it's less than this value.

#### `maxItems`
Override `maxItems` if it's greater than this value.

### `alwaysFakeOptionals`

If enabled, every property or item will be generated regardless if they are required or not. **Default**: `false`

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

People:
- [Can Vural](https://github.com/canvural)
- [All Contributors](../../contributors)

Resources:
- [cebe/php-openapi](https://github.com/cebe/php-openapi)
- [league/openapi-psr7-validator](https://github.com/thephpleague/openapi-psr7-validator)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
