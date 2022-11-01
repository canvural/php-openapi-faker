<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\ResponseFaker;
use Vural\OpenAPIFaker\Tests\MediaTypeFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

/**
 * @uses \Vural\OpenAPIFaker\Options
 * @uses \Vural\OpenAPIFaker\SchemaFaker\NumberFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\ObjectFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\SchemaFaker
 * @uses \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ResponseFaker
 */
class ResponseFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = new Options();
    }

    /** @test */
    function it_will_mock_the_response()
    {
        $yaml = <<<'YAML'
schema:
  type: object
  required:
    - todo
  properties:
    todo:
      type: object
      required:
        - id
        - name
      properties:
        id:
          type: integer
          format: int64
        name:
          type: string
        tag:
          type: string
YAML;

        $fakeData = (new ResponseFaker(MediaTypeFactory::fromYaml($yaml), $this->options))->generate();

        $this->assertMatchesJsonSnapshot($fakeData);
    }
}
