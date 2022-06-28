<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\Utils;

use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;
use Vural\OpenAPIFaker\Utils\NumberUtils;

/**
 * @covers \Vural\OpenAPIFaker\Utils\NumberUtils
 */
class NumberUtilsTest extends UnitTestCase
{
    /** @test */
    function it_can_ensure_range()
    {
        $number = NumberUtils::ensureRange(100, null, null);

        self::assertEquals(100, $number);
    }
}