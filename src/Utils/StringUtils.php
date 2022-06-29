<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Utils;

use function base_convert;
use function ceil;
use function implode;
use function max;
use function Safe\substr;
use function Safe\unpack;
use function str_repeat;
use function str_split;
use function strlen;

/**
 * @internal
 */
final class StringUtils
{
    public static function convertToBinary(string $text): string
    {
        $characters = str_split($text);

        $binary = [];
        foreach ($characters as $character) {
            $data     = unpack('H*', $character);
            $binary[] = base_convert($data[1], 16, 2);
        }

        return implode(' ', $binary);
    }

    public static function ensureLength(string $text, ?int $minLength = null, ?int $maxLength = null): string
    {
        if ($minLength === null) {
            $minLength = 0;
        }

        if ($maxLength === null) {
            $maxLength = strlen($text);
        }

        if (max($minLength, $maxLength) === 0) {
            return $text;
        }

        if ($maxLength < $minLength) {
            $maxLength = $minLength;
        }

        if (strlen($text) < $minLength) {
            $text = str_repeat($text, (int) ceil($minLength / strlen($text)));
        }

        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength);
        }

        return $text;
    }
}
