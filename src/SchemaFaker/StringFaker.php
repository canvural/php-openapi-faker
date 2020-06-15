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
use function max;
use function Safe\substr;
use function strlen;
use const DATE_RFC3339;

/**
 * @internal
 */
final class StringFaker
{
    public static function generate(Schema $schema) : string
    {
        if ($schema->enum !== null) {
            return Base::randomElement($schema->enum);
        }

        if ($schema->format !== null) {
            return self::generateFromFormat($schema);
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

    private static function generateFromFormat(Schema $schema) : string
    {
        switch ($schema->format) {
            case 'date':
                return DateTime::date();
            case 'date-time':
                return DateTime::dateTime()->format(DATE_RFC3339);
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
            default:
                return Lorem::word();
        }
    }
}
