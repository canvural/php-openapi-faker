<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker;

final class Options
{
    private int|null $minItems        = null;
    private int|null $maxItems        = null;
    private bool $alwaysFakeOptionals = false;

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

    public function getMinItems(): int|null
    {
        return $this->minItems;
    }

    public function getMaxItems(): int|null
    {
        return $this->maxItems;
    }

    public function getAlwaysFakeOptionals(): bool
    {
        return $this->alwaysFakeOptionals;
    }
}
