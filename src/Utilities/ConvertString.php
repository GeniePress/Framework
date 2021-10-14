<?php

namespace GeniePress\Utilities;

use GeniePress\Library\EnglishInflector;

/**
 * Class ConvertString
 * (string) ConvertString::From('Monkey Bar)->toPlural()->toTitleCase()
 * Notice the string coercion.
 * OR
 * ConvertString::From('Monkey Bar)->toPlural()->toTitleCase()->return();
 *
 * @package GeniePress
 */
class ConvertString
{

    /**
     * An array that hold the words in a string
     *
     * @var array
     */
    protected $words = [];



    /**
     * ConvertString constructor.
     *
     * @param  string  $string
     * @param  string|null  $case
     */
    public function __construct(string $string, string $case = null)
    {
        if ($case === 'pascalCase') {
            $string = strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string), '_'));
        }

        $this->convertToArray($string);
    }



    /**
     * static constructor
     *
     * @param  string  $string
     * @param  string|null  $case
     *
     * @return ConvertString
     */
    public static function from(string $string, string $case = null): ConvertString
    {
        return new static($string, $case);
    }



    /**
     * Magic method.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->return();
    }



    /**
     * @param  string  $with
     *
     * @return string
     */
    public function return(string $with = ' '): string
    {
        return implode($with, $this->words);
    }



    /**
     * Turn a string into camelCase
     *
     * @return string
     */
    public function toCamelCase(): string
    {
        $this->toTitleCase();
        $this->words[0] = strtolower($this->words[0]);

        return $this->return('');
    }



    /**
     * Convert to lower case
     *
     * @return ConvertString
     */
    public function toLowerCase(): ConvertString
    {
        array_walk($this->words, static function (&$word) {
            $word = strtolower($word);
        });

        return $this;
    }



    /**
     * Turn string into a slug
     *
     * @return string
     */
    public function toPascalCase(): string
    {
        return $this->toTitleCase()->return('');
    }



    /**
     * Convert to plural
     *
     * @return ConvertString
     */
    public function toPlural(): ConvertString
    {
        $string = EnglishInflector::pluralize((string) $this);
        $this->convertToArray($string[0]);

        return $this;
    }



    /**
     * Convert to singular
     *
     * @return ConvertString
     */
    public function toSingular(): ConvertString
    {
        $string = EnglishInflector::singularize((string) $this);
        $this->convertToArray($string[0]);

        return $this;
    }



    /**
     * Turn string into a slug
     *
     * @return string
     */
    public function toSlug(): string
    {
        return $this->toLowerCase()->return('-');
    }



    /**
     * Turn string into a slug
     *
     * @return string
     */
    public function toSnakeCase(): string
    {
        return $this->toLowerCase()->return('_');
    }



    /**
     * Convert to title case
     *
     * @return ConvertString
     */
    public function toTitleCase(): ConvertString
    {
        $ignoreWords = [
            'of',
            'a',
            'the',
            'and',
            'an',
            'or',
            'nor',
            'but',
            'is',
            'if',
            'then',
            'else',
            'when',
            'at',
            'from',
            'by',
            'on',
            'off',
            'for',
            'in',
            'out',
            'over',
            'to',
            'into',
            'with',
            'like',
        ];
        foreach ($this->words as $key => $word) {
            // If this word is the first, or it's not one of our small words, capitalise it with function ucwords().
            if ($key === 0 || ! in_array(strtolower($word), $ignoreWords)) {
                $this->words[$key] = ucwords($word);
            }
        }

        return $this;
    }



    /**
     * Convert to upper case
     *
     * @return ConvertString
     */
    public function toUpperCase(): ConvertString
    {
        array_walk($this->words, static function (&$word) {
            $word = strtoupper($word);
        });

        return $this;
    }



    /**
     * Convert a string into an array
     *
     * @param $string
     */
    private function convertToArray($string): void
    {
        $string = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $string);
        $string = preg_replace('/[\-_]/', ' ', $string);
        $string = preg_replace('!\s+!', ' ', $string);

        $this->words = explode(' ', trim($string));
    }

}
