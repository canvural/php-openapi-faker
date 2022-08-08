<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit;

use InvalidArgumentException;
use Vural\OpenAPIFaker\Options;

/**
 * @covers \Vural\OpenAPIFaker\Options
 */
class OptionsTest extends UnitTestCase
{
    /** @test */
    function it_can_create_with_default_values()
    {
        $options = new Options();

        self::assertNull($options->getMinItems());
        self::assertNull($options->getMaxItems());
        self::assertFalse($options->getAlwaysFakeOptionals());
        self::assertEquals(Options::STRATEGY_DYNAMIC, $options->getStrategy());
        self::assertNull($options->getExample());
    }

    /** @test */
    function it_can_set_valid_strategy_value()
    {
        $options = new Options();

        self::assertEquals(Options::STRATEGY_DYNAMIC, $options->getStrategy());

        $options->setStrategy(Options::STRATEGY_STATIC);

        self::assertEquals(Options::STRATEGY_STATIC, $options->getStrategy());
    }

    /** @test */
    function it_can_not_set_invalid_strategy_value()
    {
        $options = new Options();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown generation strategy: INVALID_STRATEGY');

        $options->setStrategy('INVALID_STRATEGY');
    }

    /** @test */
    function it_can_set_example_value()
    {
        $options = new Options();

        self::assertNull($options->getExample());

        $options->setExample('textExample');

        self::assertEquals('textExample', $options->getExample());
    }
}
