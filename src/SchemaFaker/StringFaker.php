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

use function base64_encode;
use function base_convert;
use function explode;
use function implode;
use function max;
use function preg_replace_callback;
use function reset;
use function round;
use function Safe\preg_replace;
use function Safe\substr;
use function Safe\unpack;
use function str_repeat;
use function str_replace;
use function str_split;
use function strlen;

use const DATE_RFC3339;

/**
 * @internal
 */
final class StringFaker
{
    public static function generate(Schema $schema, Options $options): ?string
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

        $min = $schema->minLength ?? 0;
        $max = $schema->maxLength ?? max(140, $min + 1);

        $result = Lorem::word();

        while (strlen($result) < $min) {
            $result .= Lorem::word();
        }

        if (strlen($result) > $max) {
            $result = substr($result, 0, $max);
        }

        return $result;
    }

    private static function generateDynamicFromFormat(Schema $schema): string
    {
        switch ($schema->format) {
            case 'date':
                return DateTime::date('Y-m-d', '2199-01-01');

            case 'date-time':
                return DateTime::dateTime('2199-01-01 00:00:00')->format(DATE_RFC3339);

            case 'email':
                return (new Internet(Factory::create()))->safeEmail();

            case 'uuid':
                return Uuid::uuid();

            case 'uri':
                return (new Internet(Factory::create()))->url();

            case 'hostname':
                return (new Internet(Factory::create()))->domainName();

            case 'ipv4':
                return (new Internet(Factory::create()))->ipv4();

            case 'ipv6':
                return (new Internet(Factory::create()))->ipv6();

            case 'byte':
                return base64_encode(Lorem::word());

            case 'binary':
                return self::stringToBinary(Lorem::word());

            case 'password':
                $min = $schema->minLength ?? 0;
                $max = $schema->maxLength ?? max(10, $min + 1);

                return Base::asciify(str_repeat('*', $max));

            default:
                return Lorem::word();
        }
    }

    private static function generateStatic(Schema $schema): ?string
    {
        if (! empty($schema->default)) {
            return $schema->default;
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
            return self::generateRegxSample($schema->pattern);
        }

        return self::ensureStringLength('string', $schema);
    }

    private static function generateStaticFromFormat(Schema $schema): string
    {
        switch ($schema->format) {
            case 'date':
                return '2019-08-24';

            case 'date-time':
                return (new \Safe\DateTime('2019-08-24T14:15:22'))->format(DATE_RFC3339);

            case 'email':
                return 'user@example.com';

            case 'uuid':
                return '095be615-a8ad-4c33-8e9c-c7612fbf6c9f';

            case 'uri':
                return 'http://example.com';

            case 'hostname':
                return 'example.com';

            case 'ipv4':
                return '192.168.0.1';

            case 'ipv6':
                return '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

            case 'byte':
                return base64_encode('string');

            case 'binary':
                return self::stringToBinary('string');

            case 'password':
                return self::generatePasswordSample($schema);

            default:
                return self::ensureStringLength('string', $schema);
        }
    }

    private static function generatePasswordSample(Schema $schema): string
    {
        $passwordSymbols = 'qwerty!@#$%^123456';
        $password        = 'pa$$word';

        $min = $schema->minLength ?? 0;

        if ($min > strlen($password)) {
            $password .= '_';
            $password .= self::ensureStringLength($passwordSymbols, $schema);
        }

        return $password;
    }

    private static function generateRegxSample(string $regex): string
    {
        // ditch the anchors
        $regex = preg_replace('/^\/?\^?/', '', $regex);
        $regex = preg_replace('/\$?\/?$/', '', $regex);
        // All {2} become {2,2}
        $regex = preg_replace('/\{(\d+)\}/', '{\1,\1}', $regex);
        // Single-letter quantifiers (?, *, +) become bracket quantifiers ({1,1})
        $regex = preg_replace('/(?<!\\\)\?/', '{1,1}', $regex);
        $regex = preg_replace('/(?<!\\\)\*/', '{1,1}', $regex);
        $regex = preg_replace('/(?<!\\\)\+/', '{1,1}', $regex);
        // [12]{1,2} becomes [12]
        $regex = preg_replace_callback('/(\[[^\]]+\])\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex);
        // (12|34){1,2} becomes (12|34)
        $regex = preg_replace_callback('/(\([^\)]+\))\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex ?? '');
        // A{1,2} becomes A or \d{3} becomes \d\d\d
        $regex = preg_replace_callback('/(\\\?.)\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex ?? '');
        // (this|that) becomes 'this'
        $regex = preg_replace_callback('/\((.*?)\)/', static function ($matches) {
            return explode('|', str_replace(['(', ')'], '', $matches[1]))[0];
        }, $regex ?? '');
        // [A-F] become [A] or [0-9] becomes [0]
        $regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {
            return '[' . preg_replace_callback('/(\w|\d)\-(\w|\d)/', static function ($range) {
                return $range[1];
            }, $matches[1]) . ']';
        }, $regex ?? '');
        // All [ABC] become A
        $regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {
            // remove backslashes (that are not followed by another backslash) because they are escape characters
            $match        = preg_replace('/\\\(?!\\\)/', '', $matches[1]);
            $firstElement = str_split($match)[0];

            //[.] should not be a character, but a literal .
            return str_replace('.', '\.', $firstElement);
        }, $regex ?? '');
        // replace \d with number 1 and \w with letter a
        $regex = preg_replace('/\\\w/', 'a', $regex ?? '');
        $regex = preg_replace('/\\\d/', '1', $regex);
        //replace . with !
        $regex = preg_replace('/(?<!\\\)\./', '!', $regex);
        // remove remaining single backslashes
        $regex = str_replace('\\\\', '[:escaped_backslash:]', $regex);
        $regex = str_replace('\\', '', $regex);
        $regex = str_replace('[:escaped_backslash:]', '\\', $regex);

        return $regex;
    }

    private static function stringToBinary(string $string): string
    {
        $characters = str_split($string);

        $binary = [];
        foreach ($characters as $character) {
            $data     = unpack('H*', $character);
            $binary[] = base_convert($data[1], 16, 2);
        }

        return implode(' ', $binary);
    }

    private static function ensureStringLength(string $sample, Schema $schema): string
    {
        $min = $schema->minLength ?? 0;
        $max = $schema->maxLength ?? max(140, $min + 1);

        if (strlen($sample) < $min) {
            $sample = str_repeat($sample, (int) round($min / strlen($sample)));
            $sample = substr($sample, 0, $min);
        }

        if (strlen($sample) > $max) {
            $sample = substr($sample, 0, $max);
        }

        return $sample;
    }
}
