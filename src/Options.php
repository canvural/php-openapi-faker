<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker;

use InvalidArgumentException;

use function in_array;
use function Safe\sprintf;

final class Options
{
    public const STRATEGY_STATIC  = 'static';
    public const STRATEGY_DYNAMIC = 'dynamic';

    private ?int $minItems            = null;
    private ?int $maxItems            = null;
    private bool $alwaysFakeOptionals = false;
    private string $strategy          = self::STRATEGY_DYNAMIC;

    public function setMinItems(int $minItems): Options
    {
        $this->minItems = $minItems;

        return $this;
    }

    public function setMaxItems(int $maxItems): Options
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function setAlwaysFakeOptionals(bool $alwaysFakeOptionals): self
    {
        $this->alwaysFakeOptionals = $alwaysFakeOptionals;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setStrategy(string $strategy): self
    {
        $allowed = [self::STRATEGY_STATIC, self::STRATEGY_DYNAMIC];

        if (! in_array($strategy, $allowed, true)) {
            throw new InvalidArgumentException(sprintf('Unknown generation strategy: %s', $strategy));
        }

        $this->strategy = $strategy;

        return $this;
    }

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function getAlwaysFakeOptionals(): bool
    {
        return $this->alwaysFakeOptionals;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
