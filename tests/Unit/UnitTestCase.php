<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function mt_srand;

use const MT_RAND_PHP;

class UnitTestCase extends TestCase
{
    use MatchesSnapshots;

    public function setUp(): void
    {
        parent::setUp();

        // Use predefined seed, so we can make realistic assertions
        mt_srand((int) 9175, MT_RAND_PHP);
    }
}
