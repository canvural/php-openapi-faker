<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Vural\OpenAPIFaker\Exception\NoPath;
use Vural\OpenAPIFaker\Exception\NoRequest;
use Vural\OpenAPIFaker\Exception\NoResponse;
use Vural\OpenAPIFaker\Exception\NoSchema;
use Vural\OpenAPIFaker\OpenAPIFaker;
use function count;

class OpenAPIFakerTest extends TestCase
{
    /**
     * @test
     * @covers \Vural\OpenAPIFaker\OpenAPIFaker::createFromJson
     */
    function it_can_create_faker_from_json()
    {
        $specJson = <<<'JSON'
{
  "openapi": "3.0.2",
  "paths": {
    "/todos": {
      "get": {
        "responses": {
          "200": {
            "description": "Get Todo Items",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Todos"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "Todo": {
        "type": "object",
        "required": [
          "id",
          "name"
        ],
        "properties": {
          "id": {
            "type": "integer",
            "format": "int64"
          },
          "name": {
            "type": "string"
          },
          "tag": {
            "type": "string"
          }
        }
      },
      "Todos": {
        "type": "array",
        "items": {
          "$ref": "#/components/schemas/Todo"
        }
      }
    }
  }
}
JSON;

        $faker = OpenAPIFaker::createFromJson($specJson);

        $this->assertInstanceOf(OpenAPIFaker::class, $faker);
    }

    /**
     * @test
     * @covers \Vural\OpenAPIFaker\OpenAPIFaker::createFromYaml
     */
    function it_can_create_faker_from_yaml()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Todo'
          application/xml:
            schema:
              $ref: '#/components/schemas/Todo'
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'

YAML;

        $faker = OpenAPIFaker::createFromYaml($specYaml);

        $this->assertInstanceOf(OpenAPIFaker::class, $faker);
    }

    /**
     * @test
     * @testWith
     *      ["/todos", "get"]
     *      ["/todos", "post", "text/plain"]
     */
    function it_throws_exception_if_request_cannot_be_found(string $path, string $method, string $contentType = 'application/json')
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Todo'
          application/xml:
            schema:
              $ref: '#/components/schemas/Todo'
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        $this->expectException(NoRequest::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockRequest($path, $method, $contentType);
    }

    /**
     * @test
     * @testWith
     *      ["/todos", "post"]
     *      ["/todos", "get", "201"]
     *      ["/todos", "get", "200", "text/plain"]
     */
    function it_throws_exception_if_response_cannot_be_found(string $path, string $method, string $statusCode = '200', string $contentType = 'application/json')
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        $this->expectException(NoResponse::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockResponse($path, $method, $statusCode, $contentType);
    }

    /** @test */
    function it_throws_exception_if_operation_not_found_in_responses()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        $this->expectException(NoPath::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockResponse('/todoss', 'GET');
    }

    /** @test */
    function it_throws_exception_if_operation_not_found_in_requests()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;

        $this->expectException(NoPath::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockRequest('/todoss', 'GET');
    }

    /** @test */
    function it_can_mock_a_specific_schema()
    {
        $specYaml = $this->getTodosSpec();

        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $todo  = $faker->mockComponentSchema('Todo');

        $this->assertIsArray($todo);
        $this->assertArrayHasKey('id', $todo);
        $this->assertIsInt($todo['id']);
        $this->assertArrayHasKey('name', $todo);
        $this->assertIsString($todo['name']);
    }

    /** @test */
    function it_will_mock_the_response()
    {
        $yamlSpec =
            <<<YAML
openapi: 3.0.2
paths:
  /todos:
    get:
      responses:
        200:
          description: Get Todo Items
          content:
            'application/json':
              schema:
                \$ref: "#/components/schemas/Todos"
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        \$ref: "#/components/schemas/Todo"
YAML;

        $fakeData = OpenAPIFaker::createFromYaml($yamlSpec)->mockResponse('/todos', 'GET');

        $this->assertIsArray($fakeData);
        $this->assertGreaterThanOrEqual(0, count($fakeData));

        foreach ($fakeData as $fakeDatum) {
            $this->assertIsArray($fakeDatum);
            $this->assertArrayHasKey('id', $fakeDatum);
            $this->assertIsInt($fakeDatum['id']);
            $this->assertArrayHasKey('name', $fakeDatum);
            $this->assertIsString($fakeDatum['name']);
        }
    }

    /** @test */
    function it_will_throw_exception_if_spec_does_not_have_any_components()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
YAML;

        $this->expectException(NoSchema::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockComponentSchema('DummySchema');
    }

    /** @test */
    function it_will_throw_exception_if_schema_does_not_exist()
    {
        $specYaml = <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
components:
  schemas:
    Todo:
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

        $this->expectException(NoSchema::class);
        $faker = OpenAPIFaker::createFromYaml($specYaml);
        $faker->mockComponentSchema('DummySchema');
    }

    private function getTodosSpec() : string
    {
        return <<<'YAML'
openapi: 3.0.2
paths:
  /todos:
    post:
      describe: Empty
    get:
      responses:
        '200':
          description: 'Get Todo Items'
          content: 
            application/json:
              schema:
                $ref: '#/components/schemas/Todos'
components:
  schemas:
    Todo:
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
    Todos:
      type: array
      items:
        $ref: '#/components/schemas/Todo'
YAML;
    }
}
