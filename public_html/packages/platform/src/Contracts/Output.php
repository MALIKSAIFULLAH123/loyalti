<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\MetaFoxConstant;

interface Output
{
    /**
     * @param string|null $text
     * @param bool        $allowSpace
     *
     * @return array<string>
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getHashtags(?string $text, bool $allowSpace = false): array;

    /**
     * @param  string|null $hashtag
     * @param  string      $regex
     * @param  array       $replace
     * @return string|null
     */
    public function normalizeHashtag(?string $hashtag, string $regex, array $replace): ?string;

    /**
     * @param  string|null $string
     * @param  string      $uri
     * @param  string|null $templateRegex
     * @return string
     */
    public function buildHashtagLink(?string $string, string $uri, ?string $templateRegex = null): string;

    /**
     * Get short description from a string.
     * The string will be shorten by number of characters declared in $limit (default: 155 chars) and all html tags
     * will be stripped out.
     *
     * @param  string|null $string
     * @param  int         $limit
     * @param  string      $end
     * @return string
     */
    public function getDescription(?string $string, int $limit = MetaFoxConstant::CHARACTER_LIMIT, string $end = '...'): string;

    /**
     * Shorten the provided string to the specified limitation, including the length of the delimiter.
     *
     * @param string|null $string    $string the string we need to limit
     * @param int         $limit     the maximum number of characters allowed
     * @param string      $delimiter
     *
     * @return string
     */
    public function limit(?string $string, int $limit = MetaFoxConstant::CHARACTER_LIMIT, string $delimiter = '...'): string;

    /**
     * @param  string|null $string
     * @param  bool        $shouldCleanBannedWords
     * @param  bool        $isEditForm
     * @return string
     */
    public function parseItemDescription(?string $string, bool $shouldCleanBannedWords = false, bool $isEditForm = false): string;

    /**
     * Text we need to parse, usually text added via a <textarea>.
     *
     * @param string|null $string $string the string we need to parse
     *
     * @return string
     */
    public function parse(?string $string, bool $shouldCleanBannedWords = false): string;

    public function cleanScriptTag(?string $string): string;

    public function cleanStyleTag(?string $string): string;

    public function isAllowHtml(): bool;

    public function linkify(?string $string): string;

    /**
     * @param string|null          $string
     * @param array<string, mixed> $options
     *
     * @return string
     */
    public function parseUrl(?string $string, array $options = []): string;

    /**
     * @param  string|null $email
     * @param  int         $left
     * @return string|null
     */
    public function maskedEmail(?string $email, int $left = 3): ?string;

    /**
     * @param  string $content
     * @param  array  $resourceHashtags
     * @return string
     */
    public function convertResourceHashtagsToLink(string $content, array $resourceHashtags): string;
}
