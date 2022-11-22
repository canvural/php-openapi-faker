<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests;

use cebe\openapi\spec\MediaType;
use Symfony\Component\Yaml\Yaml;

use function Safe\json_decode;

final class MediaTypeFactory
{
    public static function fromJson(string $content): MediaType
    {
        return new MediaType(json_decode($content, true));
    }

    public static function fromYaml(string $content): MediaType
    {
        return new MediaType(Yaml::parse($content));
    }
}
