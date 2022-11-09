<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\PSR7\SchemaFactory\YamlFactory;
use League\OpenAPIValidation\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;
use Throwable;
use Vural\OpenAPIFaker\OpenAPIFaker;
use Vural\OpenAPIFaker\Options;

use function Safe\file_get_contents;
use function Safe\sprintf;

/** @group Integration */
class E2ETest extends TestCase
{
    /**
     * @test
     * @dataProvider specProvider
     */
    function it_can_generate_valid_request(string $filename, string $strategy)
    {
        $file  = file_get_contents(sprintf('%s/../specs/%s.yaml', __DIR__, $filename));
        $faker = OpenAPIFaker::createFromYaml($file)->setOptions(['strategy' => $strategy]);

        $schema = (new YamlFactory($file))->createSchema();

        foreach ($schema->paths->getPaths() as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                /** @var RequestBody|null $requestBody */
                $requestBody = $operation->requestBody;
                if ($requestBody === null) {
                    continue;
                }

                /**
                 * @var string $contentType
                 * @var MediaType $mediaType
                 */
                foreach ($requestBody->content as $contentType => $mediaType) {
                    /** @var Schema|null $schema */
                    $schema = $mediaType->schema;

                    if ($schema === null) {
                        continue;
                    }

                    $response = $faker->mockRequest($path, $method, $contentType);

                    try {
                        (new SchemaValidator())->validate($response, $schema);
                    } catch (Throwable $e) {
                        self::fail($e->getMessage());
                    }
                }
            }
        }

        self::assertTrue(true);
    }

    /**
     * @test
     * @dataProvider specProvider
     */
    function it_can_generate_valid_response(string $filename, string $strategy)
    {
        $file  = file_get_contents(sprintf('%s/../specs/%s.yaml', __DIR__, $filename));
        $faker = OpenAPIFaker::createFromYaml($file)->setOptions(['strategy' => $strategy]);

        $schema = (new YamlFactory($file))->createSchema();

        foreach ($schema->paths->getPaths() as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                if ($operation->responses === null) {
                    continue;
                }

                foreach ($operation->responses as $statusCode => $response) {
                    foreach ($response->content as $contentType => $mediaType) {
                        if ($mediaType->schema === null) {
                            continue;
                        }

                        if ($mediaType->schema->description !== 'Video search results') {
                            continue;
                        }

                        $response = $faker->mockResponse($path, $method, (string) $statusCode, $contentType);

                        try {
                            (new SchemaValidator())->validate($response, $mediaType->schema);
                        } catch (Throwable $e) {
                            self::fail($e->getMessage());
                        }
                    }
                }
            }
        }

        self::assertTrue(true);
    }

    /**
     * @test
     * @dataProvider specProvider
     */
    function it_can_generate_valid_component(string $filename, string $strategy)
    {
        $file  = file_get_contents(sprintf('%s/../specs/%s.yaml', __DIR__, $filename));
        $faker = OpenAPIFaker::createFromYaml($file)->setOptions(['strategy' => $strategy]);

        $schema = (new YamlFactory($file))->createSchema();

        self::assertNotNull($schema->components);

        /**
         * @var string $schemaName
         * @var Schema $schema
         */
        foreach ($schema->components->schemas as $schemaName => $schema) {
            $mockSchema = $faker->mockComponentSchema($schemaName);

            try {
                (new SchemaValidator())->validate($mockSchema, $schema);
            } catch (Throwable $e) {
                self::fail($e->getMessage());
            }
        }

        self::assertTrue(true);
    }

    /** @return string[][] */
    public function specProvider(): array
    {
        return [
            ['petstore', Options::STRATEGY_DYNAMIC],
            ['twitter', Options::STRATEGY_DYNAMIC],
            ['uber', Options::STRATEGY_DYNAMIC],
            ['uspto', Options::STRATEGY_DYNAMIC],
            ['static-example', Options::STRATEGY_DYNAMIC],
            ['petstore', Options::STRATEGY_STATIC],
            ['twitter', Options::STRATEGY_STATIC],
            ['uber', Options::STRATEGY_STATIC],
            ['uspto', Options::STRATEGY_STATIC],
            ['static-example', Options::STRATEGY_STATIC],
        ];
    }
}
