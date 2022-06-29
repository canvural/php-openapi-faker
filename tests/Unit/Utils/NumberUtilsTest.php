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

    /** @test */
    function it_can_ensure_with_minimum_range()
    {
        $number = NumberUtils::ensureRange(10, 100, null);

        self::assertEquals(100, $number);
    }

    /** @test */
    function it_can_ensure_with_minimum_and_exclusive_minimum_range()
    {
        $number = NumberUtils::ensureRange(10, 100, null, true);

        self::assertEquals(101, $number);
    }

    /** @test */
    function it_can_ensure_with_maximum_range()
    {
        $number = NumberUtils::ensureRange(100, null, 50);

        self::assertEquals(50, $number);
    }

    /** @test */
    function it_can_ensure_with_maximum_and_exclusive_maximum_range()
    {
        $number = NumberUtils::ensureRange(100, null, 50, null, true);

        self::assertEquals(49, $number);
    }

    /** @test */
    function it_can_ensure_with_minimum_and_maximum_exclusive_both_range()
    {
        $number = NumberUtils::ensureRange(40, 10, 50, true, true);

        self::assertEquals(40, $number);
    }

    /** @test */
    function it_can_ensure_with_multipl_of_range()
    {
        $number = NumberUtils::ensureRange(10, null, null, null, null, 3);

        self::assertEquals(9, $number);
    }
}
