<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker;

final class Options
{
    private ?int $minItems = null;
    private ?int $maxItems = null;

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

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }
}
