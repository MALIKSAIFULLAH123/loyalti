<?php
namespace MetaFox\Core\Contracts;

interface EmojiSupportInterface
{
    /**
     * @param string|null $string
     * @return array
     */
    public function detectEmojis(?string $string): array;

    /**
     * @param string|null $string
     * @return array|null
     */
    public function detectFirstEmoji(?string $string): ?array;

    /**
     * @param string|null $string
     * @param array       $opts
     * @return string|null
     */
    public function removeEmojis(?string $string, array $opts = []): ?string;

    /**
     * @param string $cp
     * @return bool
     */
    public function isModifier(string $cp): bool;

    /**
     * @param string $cp
     * @return bool
     */
    public function isZWJ(string $cp): bool;

    /**
     * @param string $cp
     * @return bool
     */
    public function isCountryFlag(string $cp): bool;

    /**
     * @param string $c
     * @return int|null
     */
    public function ord(string $c): ?int;
}
