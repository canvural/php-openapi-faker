<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\Utils;

use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;
use Vural\OpenAPIFaker\Utils\RegexUtils;

/** @covers \Vural\OpenAPIFaker\Utils\RegexUtils */
class RegexUtilsTest extends UnitTestCase
{
    private const SAMPLE_REGEX = '^[a-z]-[0-9]-A{2}-[ABC]{1,2}-(foo|bar){1,2}-(A)+-(\d{3}-\d{2}-\d{4})*.*$';

    /** @test */
    function it_can_convert_string_to_binary()
    {
        $sample = RegexUtils::generateSample(self::SAMPLE_REGEX);

        self::assertEquals('a-0-AA-A-foo-A-111-11-1111!', $sample);
    }
}
