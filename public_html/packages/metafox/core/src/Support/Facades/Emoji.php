<?php
namespace MetaFox\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Core\Contracts\EmojiSupportInterface;

/**
 * @method static array detectEmojis(?string $string)
 * @method static array|null detectFirstEmoji(?string $string)
 * @method static string|null removeEmojis(?string $string, array $opts = [])
 * @method static bool isModifier(string $cp)
 * @method static bool isZWJ(string $cp)
 * @method static bool isCountryFlag(string $cp)
 * @method static int|null ord(string $c)
 */
class Emoji extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EmojiSupportInterface::class;
    }
}
