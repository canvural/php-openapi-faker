<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\SchemaFaker\BooleanFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

/**
 * @covers \Vural\OpenAPIFaker\SchemaFaker\BooleanFaker
 */
class BooleanFakerTest extends UnitTestCase
{
    /** @test */
    function it_can_generate_boolean_value()
    {
        $yaml = <<<YAML
type: boolean
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml));

        $this->assertIsBool($fakeData);
    }

    /** @test */
    function it_can_generate_boolean_value_from_enum()
    {
        $yaml = <<<YAML
type: boolean
enum:
  - true
YAML;

        $fakeData = BooleanFaker::generate(SchemaFactory::fromYaml($yaml));

        $this->assertTrue($fakeData);
    }
}
