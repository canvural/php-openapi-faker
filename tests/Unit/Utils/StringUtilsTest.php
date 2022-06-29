<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\Utils;

use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;
use Vural\OpenAPIFaker\Utils\StringUtils;

use function strlen;

/**
 * @covers \Vural\OpenAPIFaker\Utils\StringUtils
 */
class StringUtilsTest extends UnitTestCase
{
    private const SAMPLE_STRING = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @test */
    function it_can_convert_string_to_binary()
    {
        $binary = StringUtils::convertToBinary('Hello World');

        self::assertEquals('1001000 1100101 1101100 1101100 1101111 100000 1010111 1101111 1110010 1101100 1100100', $binary);
    }

    /** @test */
    function it_can_ensure_string_length()
    {
        $password = StringUtils::ensureLength(self::SAMPLE_STRING, 0, 0);

        self::assertEquals(self::SAMPLE_STRING, $password);
        self::assertEquals(strlen(self::SAMPLE_STRING), strlen($password));
    }

    /** @test */
    function it_can_ensure_min_string_length()
    {
        $password = StringUtils::ensureLength(self::SAMPLE_STRING, 30, 0);

        self::assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZABCD', $password);
        self::assertEquals(30, strlen($password));
    }

    /** @test */
    function it_can_ensure_max_string_length()
    {
        $password = StringUtils::ensureLength(self::SAMPLE_STRING, 0, 5);

        self::assertEquals('ABCDE', $password);
        self::assertEquals(5, strlen($password));
    }

    /** @test */
    function it_can_ensure_min_and_max_string_length()
    {
        $password = StringUtils::ensureLength(self::SAMPLE_STRING, 5, 20);

        self::assertEquals('ABCDEFGHIJKLMNOPQRST', $password);
        self::assertEquals(20, strlen($password));
    }
}
