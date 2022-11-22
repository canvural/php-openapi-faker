<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\SchemaFaker;

use cebe\openapi\spec\Schema;
use Faker\Factory;
use Faker\Provider\Base;
use Faker\Provider\DateTime;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Faker\Provider\Uuid;
use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\Utils\RegexUtils;
use Vural\OpenAPIFaker\Utils\StringUtils;

use function base64_encode;
use function max;
use function reset;
use function str_repeat;
use function strlen;
use function substr;

use const DATE_RFC3339;

/** @internal */
final class StringFaker
{
    public static function generate(Schema $schema, Options $options): string|null
    {
        if ($options->getStrategy() === Options::STRATEGY_STATIC) {
            return self::generateStatic($schema);
        }

        return self::generateDynamic($schema);
    }

    private static function generateDynamic(Schema $schema): string
    {
        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        if ($schema->format !== null) {
            return self::generateDynamicFromFormat($schema);
        }

        if ($schema->pattern !== null) {
            return Lorem::regexify($schema->pattern);
        }

        $minLength = $schema->minLength ?? 0;
        $maxLength = $schema->maxLength ?? max(140, $minLength + 1);

        $result = Lorem::word();

        while (strlen($result) < $minLength) {
            $result .= Lorem::word();
        }

        if (strlen($result) > $maxLength) {
            $result = substr($result, 0, $maxLength);
        }

        return $result;
    }

    private static function generateDynamicFromFormat(Schema $schema): string
    {
        $minLength = $schema->minLength ?? 0;
        $maxLength = $schema->maxLength ?? max(10, $minLength + 1);

        return match ($schema->format) {
            'date' => DateTime::date('Y-m-d', '2199-01-01'),
            'date-time' => DateTime::dateTime('2199-01-01 00:00:00')->format(DATE_RFC3339),
            'email' => (new Internet(Factory::create()))->safeEmail(),
            'uuid' => Uuid::uuid(),
            'uri' => (new Internet(Factory::create()))->url(),
            'hostname' => (new Internet(Factory::create()))->domainName(),
            'ipv4' => (new Internet(Factory::create()))->ipv4(),
            'ipv6' => (new Internet(Factory::create()))->ipv6(),
            'byte' => base64_encode(Lorem::word()),
            'binary' => StringUtils::convertToBinary(Lorem::word()),
            'password' => Base::asciify(str_repeat('*', $maxLength)),
            default => Lorem::word(),
        };
    }

    private static function generateStatic(Schema $schema): string|null
    {
        if (! empty($schema->default)) {
            return $schema->default;
        }

        if ($schema->example !== null) {
            return $schema->example;
        }

        if ($schema->nullable) {
            return null;
        }

        if ($schema->enum !== null) {
            $enums = $schema->enum;

            return reset($enums);
        }

        if ($schema->format !== null) {
            return self::generateStaticFromFormat($schema);
        }

        if ($schema->pattern !== null) {
            return RegexUtils::generateSample($schema->pattern);
        }

        $minLength = $schema->minLength;
        $maxLength = $schema->maxLength;

        return StringUtils::ensureLength('string', $minLength, $maxLength);
    }

    private static function generateStaticFromFormat(Schema $schema): string
    {
        $minLength = $schema->minLength;
        $maxLength = $schema->maxLength;

        return match ($schema->format) {
            'date' => '2019-08-24',
            'date-time' => (new \Safe\DateTime('2019-08-24T14:15:22'))->format(DATE_RFC3339),
            'email' => 'user@example.com',
            'uuid' => '095be615-a8ad-4c33-8e9c-c7612fbf6c9f',
            'uri' => 'http://example.com',
            'hostname' => 'example.com',
            'ipv4' => '192.168.0.1',
            'ipv6' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'byte' => base64_encode('string'),
            'binary' => StringUtils::convertToBinary('string'),
            'password' => StringUtils::ensureLength('pa$$wordqwerty!@#$%^123456', $minLength, $maxLength),
            default => StringUtils::ensureLength('string', $minLength, $maxLength),
        };
    }
}
