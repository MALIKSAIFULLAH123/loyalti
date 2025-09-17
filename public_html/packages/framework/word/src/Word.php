<?php

namespace MetaFox\Word;

use Illuminate\Support\Facades\Facade;
use MetaFox\Word\Support\WordSupport as WordSupport;

/**
 * @see
 * @method static void addBlockWords(array $words)
 * @method static void buildBlockWords()
 * @method static bool isBlocked(string $word)
 * @see \MetaFox\Word\Support\WordSupport
 */
class Word extends Facade
{
    protected static function getFacadeAccessor()
    {
        return WordSupport::class;
    }
}
