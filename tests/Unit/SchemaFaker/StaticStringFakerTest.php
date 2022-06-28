<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit\SchemaFaker;

use DateTime;
use Vural\OpenAPIFaker\Options;
use Vural\OpenAPIFaker\SchemaFaker\StringFaker;
use Vural\OpenAPIFaker\Tests\SchemaFactory;
use Vural\OpenAPIFaker\Tests\Unit\UnitTestCase;

use function filter_var;
use function Safe\sprintf;
use function strlen;

use const FILTER_VALIDATE_URL;

/**
 * @uses \Vural\OpenAPIFaker\Options
 *
 * @covers \Vural\OpenAPIFaker\SchemaFaker\StringFaker
 * @covers \Vural\OpenAPIFaker\Utils\RegexUtils
 * @covers \Vural\OpenAPIFaker\Utils\StringUtils
 */
class StaticStringFakerTest extends UnitTestCase
{
    private Options $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = (new Options())->setStrategy(Options::STRATEGY_STATIC);
    }

    /** @test */
    function it_can_generate_single_string()
    {
        $yaml = <<<YAML
type: string
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNotNull($fakeData);
        self::assertGreaterThanOrEqual(0, strlen($fakeData));
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_min_length()
    {
        $yaml = <<<YAML
type: string
minLength: 25
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNotNull($fakeData);
        self::assertGreaterThanOrEqual(10, strlen($fakeData));
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_max_length()
    {
        $yaml = <<<YAML
type: string
maxLength: 3
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNotNull($fakeData);
        self::assertLessThanOrEqual(3, strlen($fakeData));
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_date_format()
    {
        $yaml = <<<YAML
type: string
format: date
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);

        $date = DateTime::createFromFormat('Y-m-d', $fakeData);
        self::assertNotFalse($date);
        self::assertSame($date->format('Y-m-d'), $fakeData);
    }

    /** @test */
    function it_can_handle_datetime_format()
    {
        $yaml = <<<YAML
type: string
format: date-time
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);

        $date = DateTime::createFromFormat('Y-m-d\TH:i:sP', $fakeData);
        self::assertNotFalse($date, sprintf("Failed asserting '%s' is in 'Y-m-d\TH:i:sP' format", $fakeData));
        self::assertSame($date->format('Y-m-d\TH:i:sP'), $fakeData);
    }

    /** @test */
    function it_can_handle_email_format()
    {
        $yaml = <<<YAML
type: string
format: email
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertMatchesRegularExpression('/^.+\@\S+\.\S+$/', $fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_uuid_format()
    {
        $yaml = <<<YAML
type: string
format: uuid
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertMatchesRegularExpression('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_uri_format()
    {
        $yaml = <<<YAML
type: string
format: uri
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertNotFalse(filter_var($fakeData, FILTER_VALIDATE_URL));
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_hostname_format()
    {
        $yaml = <<<YAML
type: string
format: hostname
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_ipv4_format()
    {
        $yaml = <<<YAML
type: string
format: ipv4
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertMatchesRegularExpression('/^((25[0-5]|(2[0-4]|1[0-9]|[1-9]|)[0-9])(\.(?!$)|$)){4}$/', $fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_ipv6_format()
    {
        $yaml = <<<YAML
type: string
format: ipv6
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertMatchesRegularExpression('/^([0-9A-Fa-f]{0,4}:){2,7}([0-9A-Fa-f]{1,4}$|((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4})$/', $fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_byte_format()
    {
        $yaml = <<<YAML
type: string
format: byte
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_binary_format()
    {
        $yaml = <<<YAML
type: string
format: binary
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_password_format()
    {
        $yaml = <<<YAML
type: string
format: password
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_long_password_format()
    {
        $yaml = <<<YAML
type: string
format: password
minLength: 10
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_handle_patterns()
    {
        $yaml = <<<YAML
type: string
pattern: '^[a-z]-[0-9]-A{2}-[ABC]{1,2}-(foo|bar){1,2}-(A)+-(\d{3}-\d{2}-\d{4})*.*$'
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertIsString($fakeData);
        self::assertMatchesRegularExpression('/^[a-z]-[0-9]-A{2}-[ABC]{1,2}-(foo|bar){1,2}-(A)+-(\d{3}-\d{2}-\d{4})*.*$/', $fakeData);
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_will_return_a_string_if_unknown_format_is_given()
    {
        $yaml = <<<YAML
type: string
format: unkown
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNotNull($fakeData);
        self::assertGreaterThanOrEqual(0, strlen($fakeData));
        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_string_value_from_enum()
    {
        $yaml = <<<YAML
type: string
enum:
  - foo
  - bar
  - baz
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_default_value_from_enum()
    {
        $yaml = <<<YAML
type: string
enum:
  - foo
  - bar
  - baz
default: bar
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        $this->assertMatchesSnapshot($fakeData);
    }

    /** @test */
    function it_can_generate_nullable_value()
    {
        $yaml = <<<YAML
type: string
nullable: true
YAML;

        $fakeData = StringFaker::generate(SchemaFactory::fromYaml($yaml), $this->options);

        self::assertNull($fakeData);
    }
}
