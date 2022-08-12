<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use Vural\OpenAPIFaker\Exception\NoExample;
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
 * @uses \Vural\OpenAPIFaker\Utils\NumberUtils
 * @uses \Vural\OpenAPIFaker\Utils\StringUtils
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\ResponseFaker
 * @covers \Vural\OpenAPIFaker\Exception\NoExample
 */
class StaticResponseFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_will_mock_the_response()
    {
        $yaml = <<<YAML
schema:
  type: object
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

    /** @test */
    function it_will_mock_the_first_example()
    {
        $yaml = <<<YAML
schema:
  type: object
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
examples: 
  testExample:
    summary: A todo example
    value:
      todo:
        id: 100
        name: watering plants
        tag: homework
  otherExample:
    summary: A other todo example
    value:
      todo:
        id: 101
        name: prepare food
        tag: homework
YAML;

        $fakeData = (new ResponseFaker(MediaTypeFactory::fromYaml($yaml), $this->options))->generate();

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_will_mock_the_given_example()
    {
        $yaml = <<<YAML
schema:
  type: object
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
examples: 
  testExample:
    summary: A todo example
    value:
      todo:
        id: 100
        name: watering plants
        tag: homework
  otherExample:
    summary: A other todo example
    value:
      todo:
        id: 101
        name: prepare food
        tag: homework
YAML;

        $fakeData = (new ResponseFaker(MediaTypeFactory::fromYaml($yaml), $this->options))->generate('otherExample');

        $this->assertMatchesJsonSnapshot($fakeData);
    }

    /** @test */
    function it_throws_exception_if_example_cannot_be_found()
    {
        $yaml = <<<YAML
schema:
  type: object
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
examples: 
  testExample:
    summary: A todo example
    value:
      todo:
        id: 100
        name: watering plants
        tag: homework
  otherExample:
    summary: A other todo example
    value:
      todo:
        id: 101
        name: prepare food
        tag: homework
YAML;

        $this->expectException(NoExample::class);
        $this->expectExceptionMessage('OpenAPI spec does not have a example "unknownExample" response');

        (new ResponseFaker(MediaTypeFactory::fromYaml($yaml), $this->options))->generate('unknownExample');
    }
}
