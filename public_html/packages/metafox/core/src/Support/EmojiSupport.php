<?php

namespace MetaFox\Core\Support;

use Illuminate\Support\Arr;
use MetaFox\Core\Contracts\EmojiSupportInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\PackageManager;

/**
 * @link https://github.com/aaronpk/emoji-detector-php
 */
class EmojiSupport implements EmojiSupportInterface
{
    /**
     * @var array
     */
    protected array $codePoints;

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->codePoints = localCacheStore()->rememberForever(__METHOD__, function () {
            try {
                $path = PackageManager::getPath('metafox/core');

                if (!is_string($path)) {
                    return [];
                }

                $path = rtrim(base_path($path), '/') . '/resources/emoji/base-codepoints.json';

                if (!file_exists($path)) {
                    return [];
                }

                $points = json_decode(file_get_contents($path), true);

                if (!is_array($points) || !count($points)) {
                    return [];
                }

                return $points;
            } catch (\Throwable $exception) {
                return [];
            }
        });
    }

    public function detectEmojis(?string $string): array
    {
        if (!count($this->codePoints)) {
            return [];
        }

        if (null === $string) {
            return [];
        }

        $string = trim($string);

        if (MetaFoxConstant::EMPTY_STRING === $string) {
            return [];
        }

        $originalEncoding = mb_internal_encoding();

        try {
            mb_internal_encoding('UTF-8');

            $data = [];

            $codePoints = mb_str_split($string);

            $emojiChars = [];

            $currentEmoji = null;

            $includeNext = false;

            foreach ($codePoints as $cp) {
                if (null === $currentEmoji) {
                    if (in_array($cp, $this->codePoints)) {
                        $currentEmoji = $cp;
                    } elseif ($this->isCountryFlag($cp)) {
                        $currentEmoji = $cp;
                        $includeNext = true; // Flags are always 2 chars so grab the next one
                    }
                } else {
                    if ($includeNext) {
                        $currentEmoji .= $cp;
                        $includeNext = false;
                    } elseif ($this->isModifier($cp)) {
                        // If this codepoint is a modifier, add it now
                        $currentEmoji .= $cp;
                        $includeNext = false;
                    } elseif ($this->isZWJ($cp)) {
                        // If this codepoint is a ZWJ, include the next codepoint in the emoji as well
                        $currentEmoji .= $cp;
                        $includeNext = true;
                    } else {
                        $emojiChars[] = $currentEmoji;
                        $currentEmoji = null;

                        if (in_array($cp, $this->codePoints)) {
                            $currentEmoji = $cp;
                        } elseif ($this->isCountryFlag($cp)) {
                            $currentEmoji = $cp;
                            $includeNext = true; // Flags are always 2 chars so grab the next one
                        }
                    }
                }
            }

            if ($currentEmoji) {
                $emojiChars[] = $currentEmoji;
            }

            // Now we have a list of individual completed emoji chars in the order they are in the string.

            $lastGOffset = 0;

            $lastOffset = 0;

            foreach ($emojiChars as $emoji) {
                $mbLength = mb_strlen($emoji); // the length of the emoji, mb chars are counted as 1

                $offset = strpos($string, $emoji, $lastOffset);

                $lastOffset = $offset + strlen($emoji);

                $gOffset = grapheme_strpos($string, $emoji, $lastGOffset);

                $lastGOffset = $gOffset + 1;

                $points = [];

                for ($i = 0; $i < $mbLength; $i++) {
                    $decimal = $this->ord(mb_substr($emoji, $i, 1));

                    if (null === $decimal) {
                        continue;
                    }

                    $points[] = strtoupper(dechex($decimal));
                }

                $hexStr = implode('-', $points);

                $skinTone = null;

                $skinTones = [
                    '1F3FB' => 'skin-tone-2',
                    '1F3FC' => 'skin-tone-3',
                    '1F3FD' => 'skin-tone-4',
                    '1F3FE' => 'skin-tone-5',
                    '1F3FF' => 'skin-tone-6',
                ];

                foreach ($points as $pt) {
                    if (!array_key_exists($pt, $skinTones)) {
                        continue;
                    }

                    $skinTone = $skinTones[$pt];
                }

                $data[] = [
                    'emoji'                  => $emoji,
                    'total_codepoints'       => mb_strlen($emoji),
                    'hexadecimal_codepoints' => $points,
                    'emoji_codepoint'        => $hexStr,
                    'skin_tone'              => $skinTone,
                    'byte_offset'            => $offset,       // The position of the emoji in the string, counting each byte
                    'grapheme_offset'        => $gOffset,  // The grapheme-based position of the emoji in the string
                ];
            }

            if ($originalEncoding) {
                mb_internal_encoding($originalEncoding);
            }

            return $data;
        } catch (\Throwable $exception) {
            if ($originalEncoding) {
                mb_internal_encoding($originalEncoding);
            }

            return [];
        }
    }

    public function detectFirstEmoji(?string $string): ?array
    {
        if (null === $string) {
            return null;
        }

        $emojis = $this->detectEmojis($string);

        if (!count($emojis)) {
            return null;
        }

        return array_shift($emojis);
    }

    public function removeEmojis(?string $string, array $opts = []): ?string
    {
        if (null === $string) {
            return null;
        }

        $emojis = $this->detectEmojis($string);

        foreach (array_reverse($emojis) as $emoji) {
            $length = strlen($emoji['emoji']);

            $start = substr($string, 0, $emoji['byte_offset']);

            $end = substr($string, $emoji['byte_offset'] + $length, strlen($string) - ($emoji['byte_offset'] + $length));

            if (is_bool(Arr::get($opts, 'collapse'))) {
                $end = trim($end);
            }

            $string = $start . $end;
        }

        return $string;
    }

    public function isModifier(string $cp): bool
    {
        $modifiers = [
            "\u{1F3FB}",
            "\u{1F3FC}",
            "\u{1F3FD}",
            "\u{1F3FE}",
            "\u{1F3FF}",
            "\u{FE0F}",
            "\u{E0067}",
            "\u{E0062}",
            "\u{E0063}",
            "\u{E0065}",
            "\u{E006C}",
            "\u{E006E}",
            "\u{E0073}",
            "\u{E0074}",
            "\u{E0077}",
            "\u{E007F}",
        ];

        return in_array($cp, $modifiers);
    }

    public function isZWJ(string $cp): bool
    {
        return "\u{200D}" === $cp;
    }

    public function isCountryFlag(string $cp): bool
    {
        return mb_ord("\u{1F1E6}") <= mb_ord($cp) && mb_ord($cp) <= mb_ord("\u{1F1FF}");
    }

    public function ord(string $c): ?int
    {
        try {
            $ord0 = ord($c[0]);

            if ($ord0 <= 127) {
                return $ord0;
            }

            $ord1 = ord($c[1]);

            if ($ord0 >= 192 && $ord0 <= 223) {
                return ($ord0 - 192) * 64 + ($ord1 - 128);
            }

            $ord2 = ord($c[2]);

            if ($ord0 >= 224 && $ord0 <= 239) {
                return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
            }

            $ord3 = ord($c[3]);

            if ($ord0 >= 240 && $ord0 <= 247) {
                return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
            }
        } catch (\Throwable $exception) {}

        return null;
    }
}
