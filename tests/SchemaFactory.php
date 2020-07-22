<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests;

use cebe\openapi\spec\Schema;
use Symfony\Component\Yaml\Yaml;

use function Safe\json_decode;

final class SchemaFactory
{
    public static function fromJson(string $content): Schema
    {
        return new Schema(json_decode($content, true));
    }

    public static function fromYaml(string $content): Schema
    {
        return new Schema(Yaml::parse($content));
    }
}
