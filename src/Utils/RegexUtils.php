<?php

declare(strict_types=1);

namespace Vural\OpenAPIFaker\Utils;

use function explode;
use function preg_replace_callback;
use function Safe\preg_replace;
use function str_repeat;
use function str_replace;
use function str_split;

/**
 * @internal
 */
final class RegexUtils
{
    public static function generateSample(string $regex): string
    {
        // ditch the anchors
        $regex = preg_replace('/^\/?\^?/', '', $regex);
        $regex = preg_replace('/\$?\/?$/', '', $regex);
        // All {2} become {2,2}
        $regex = preg_replace('/\{(\d+)\}/', '{\1,\1}', $regex);
        // Single-letter quantifiers (?, *, +) become bracket quantifiers ({1,1})
        $regex = preg_replace('/(?<!\\\)\?/', '{1,1}', $regex);
        $regex = preg_replace('/(?<!\\\)\*/', '{1,1}', $regex);
        $regex = preg_replace('/(?<!\\\)\+/', '{1,1}', $regex);
        // [12]{1,2} becomes [12]
        $regex = preg_replace_callback('/(\[[^\]]+\])\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex);
        // (12|34){1,2} becomes (12|34)
        $regex = preg_replace_callback('/(\([^\)]+\))\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex ?? '');
        // A{1,2} becomes A or \d{3} becomes \d\d\d
        $regex = preg_replace_callback('/(\\\?.)\{(\d+),(\d+)\}/', static function ($matches) {
            return str_repeat($matches[1], (int) $matches[2]);
        }, $regex ?? '');
        // (this|that) becomes 'this'
        $regex = preg_replace_callback('/\((.*?)\)/', static function ($matches) {
            return explode('|', str_replace(['(', ')'], '', $matches[1]))[0];
        }, $regex ?? '');
        // [A-F] become [A] or [0-9] becomes [0]
        $regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {
            return '[' . preg_replace_callback('/(\w|\d)\-(\w|\d)/', static function ($range) {
                return $range[1];
            }, $matches[1]) . ']';
        }, $regex ?? '');
        // All [ABC] become A
        $regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {
            // remove backslashes (that are not followed by another backslash) because they are escape characters
            $match        = preg_replace('/\\\(?!\\\)/', '', $matches[1]);
            $firstElement = str_split($match)[0];

            //[.] should not be a character, but a literal .
            return str_replace('.', '\.', $firstElement);
        }, $regex ?? '');
        // replace \d with number 1 and \w with letter a
        $regex = preg_replace('/\\\w/', 'a', $regex ?? '');
        $regex = preg_replace('/\\\d/', '1', $regex);
        //replace . with !
        $regex = preg_replace('/(?<!\\\)\./', '!', $regex);
        // remove remaining single backslashes
        $regex = str_replace('\\\\', '[:escaped_backslash:]', $regex);
        $regex = str_replace('\\', '', $regex);
        $regex = str_replace('[:escaped_backslash:]', '\\', $regex);

        return $regex;
    }
}
