<?php

namespace MetaFox\Core\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Emoji;
use MetaFox\Platform\Contracts\Output as OutputContract;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

class Output implements OutputContract
{
    public const HASHTAG_REGEX          = '([#﹟＃](?:(?:[^\s!@#$%^&*()=\-+.\/,\[{\]};:\'"?><]+)?[^\s!@#$%^&*()=\-+.\/,\[{\]};:\'"?><\d_](?:[^\s!@#$%^&*()=\-+.\/,\[{\]};:\'"?><]+)?))';
    public const NUMBER_SIGN_CHARACTERS = ['#', '﹟', '＃']; // This is 3 different characters.
    public const TOPIC_REGEX            = '(##[^!@#$%^&*()=\-+.\/,\[{\]};:\'"?><]+[^\r\n\t\f\v]+)';
    public const HASHTAG_LINK           = '<a href="%s">%s</a>';
    public const URL_REGEX              = '@(http(s)?)?(:\/\/)?((([a-zA-Z0-9]+)([-\w]*\.)*)?([-\w]+\.[a-zA-Z]{1,})([\w=%]+\S*)+)@';
    public const PARSE_URL_IGNORE_TAGS  = 'head|link|a|script|style|code|pre|select|textarea|button';
    public const TEXT_NEW_LINE_REGEX    = '@<(\/p|br)>@';

    public const URL_POPULAR_DOMAIN_REGEX = '@\w+\.(com|gov|vn|net)$@'; // Todo: need to move this to a full support list of domain?

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getHashtags(?string $text, bool $allowSpace = false): array
    {
        if (null === $text) {
            return [];
        }

        //Remove "#<something>" part of a url.
        $linkRegex  = '/(http[s]?:\/\/(www\.)?|ftp:\/\/(www\.)?|www\.){1}([0-9A-Za-z-\-\.@:%_\+~#=]+)+((\.[a-zA-Z]{2,3})+)(\/([0-9A-Za-z-\-\.@:%_\+~#=\?])*)*/i';
        $styleRegex = '/style=(\'|")[^\'"]+(\'|")/i';
        $text       = preg_replace($linkRegex, '', $text);
        $text       = preg_replace($styleRegex, '', $text);
        $text       = trim($text);

        if (in_array($text, [null, MetaFoxConstant::EMPTY_STRING])) {
            return [];
        }

        $regex   = self::HASHTAG_REGEX;

        $replace = self::NUMBER_SIGN_CHARACTERS;

        if ($allowSpace) {
            $regex   = self::TOPIC_REGEX;
            $replace = array_values(array_map(fn ($character) => $character . $character, self::NUMBER_SIGN_CHARACTERS));
        }

        //Search for hashtags
        $matches = Str::of($text)
            ->matchAll($regex)
            ->map(function (string $hashtag) use ($replace, $regex) {
                return $this->normalizeHashtag($hashtag, $regex, $replace);
            });

        return $matches->filter()->toArray();
    }

    public function normalizeHashtag(?string $hashtag, string $regex, array $replace): ?string
    {
        if (null === $hashtag) {
            return null;
        }

        if (!Str::startsWith($hashtag, $replace)) {
            return null;
        }

        $text = Str::replace($replace, '', $hashtag);

        $firstEmoji = Emoji::detectFirstEmoji($text);

        if (null === $firstEmoji) {
            return $text;
        }

        $byteOffset = Arr::get($firstEmoji, 'byte_offset');

        if (0 === $byteOffset) {
            return null;
        }

        $text = substr($text, 0, $byteOffset);

        if (!preg_match($regex, sprintf('#%s', $text))) {
            return null;
        }

        return $text;
    }

    public function buildHashtagLink(?string $string, string $uri, ?string $templateRegex = null): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $url = '/hashtag/search?q=' . $uri;

        $linkRegex = $templateRegex ?? self::HASHTAG_LINK;

        return sprintf($linkRegex, $url, $string);
    }

    /**
     * @inerhitDoc
     */
    public function getDescription(?string $string, int $limit = MetaFoxConstant::CHARACTER_LIMIT, string $end = '...'): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        // Parse any new line tags into end of line char
        $string = $this->handleNewLineTag($string);

        $string = strip_tags($string);
        $string = str_replace('&nbsp;', '', $string);

        return Str::limit($string, $limit, $end);
    }

    /**
     * @inerhitDoc
     */
    public function limit(?string $string, int $limit = MetaFoxConstant::CHARACTER_LIMIT, string $delimiter = '...'): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return Str::limit($string, $limit - mb_strlen($delimiter), $delimiter);
    }

    public function parseItemDescription(?string $string, bool $shouldCleanBannedWords = false, bool $isEditForm = false): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        /**
         * Always clean script & style tags.
         */
        $string = $this->cleanScriptTag($string);

        $string = $this->cleanStyleTag($string);

        $isAllowHtml = $this->isAllowHtml();

        if (!$isAllowHtml) {
            $string = strip_tags($string);
        }

        if ($shouldCleanBannedWords) {
            $string = ban_word()->clean($string);
        }

        $string = ban_word()->parse($string);

        return trim($string);
    }

    public function parse(?string $string, bool $shouldCleanBannedWords = false): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        /**
         * Always clean script & style tags.
         */
        $string = $this->cleanScriptTag($string);

        $string = $this->cleanStyleTag($string);

        $isAllowHtml = $this->isAllowHtml();

        if (!$isAllowHtml) {
            $string = strip_tags($string);
        }

        if ($shouldCleanBannedWords) {
            $string = ban_word()->clean($string);
        }

        $string = ban_word()->parse($string);

        $string = $this->parseUrl($string);

        return trim($string);
    }

    public function cleanScriptTag(?string $string): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        /** @var string $parsedText */
        $parsedText = preg_replace("/<script([^\>]*)>/uim", '', $string);
        $parsedText = preg_replace("/<\/script>/uim", '', $parsedText);

        if (null === $parsedText) {
            return $string;
        }

        return $parsedText;
    }

    public function cleanStyleTag(?string $string): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        /** @var string $parsedText */
        $parsedText = preg_replace("/<style([^\>]*)>/uim", '', $string);
        $parsedText = preg_replace("/<\/style>/uim", '', $parsedText);

        if (null === $parsedText) {
            return $string;
        }

        return $parsedText;
    }

    public function isAllowHtml(): bool
    {
        return true;

        //return Settings::get('core.general.allow_html', true);
    }

    /**
     * @param string|null          $string  $string
     * @param array<string, mixed> $options
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function parseUrl(?string $string, array $options = []): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $ignoreTags = self::PARSE_URL_IGNORE_TAGS;
        $chunks     = preg_split('/(<.+?>|\s+)/im', $string, 0, PREG_SPLIT_DELIM_CAPTURE);

        if (false === $chunks) {
            return $this->linkify($string);
        }

        $chunkLength = count($chunks);
        for ($i = 0; $i < $chunkLength; $i++) {
            $isTextNode = !preg_match("@(<($ignoreTags).*(?<!\/)>|\s+)$@is", $chunks[$i]);
            if ($isTextNode) {
                $chunks[$i] = $this->linkify($chunks[$i]);
            }
        }

        return implode($chunks);
    }

    /**
     * @param string|null $string
     *
     * @return string
     * @todo: should move to a parse_link service?
     */
    public function linkify(?string $string): string
    {
        if (null === $string) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        if (preg_match(sprintf('/%s/', MetaFoxConstant::EMAIL_REGEX), $string)) {
            return $this->parseEmailLink($this->removeNonBreakingSpaceCharacter($string));
        }

        if ($this->isValidLink($string)) {
            return $this->parseUrlLink($this->removeNonBreakingSpaceCharacter($string));
        }

        return $string;
    }

    protected function removeNonBreakingSpaceCharacter(string $string): string
    {
        return str_replace('&nbsp;', '', $string);
    }

    private function parseEmailLink(string $string): string
    {
        $parsed = preg_replace(
            sprintf('/%s/', MetaFoxConstant::EMAIL_REGEX),
            '<a rel="noopener" href="mailto:$0" title="$0">$0</a>',
            $string
        );

        return $parsed ?? '';
    }

    private function parseUrlLink(string $string): string
    {
        $parsed = preg_replace(
            self::URL_REGEX,
            '<a rel="noopener" href="http$2://$4" target="_blank" title="$0">$0</a>',
            $string
        );

        return $parsed ?? '';
    }

    /**
     * @param string $string
     *
     * @return bool
     * @Todo: Should be extend to support a list of all active link?
     */
    private function isValidLink(string $string): bool
    {
        // If a URL Scheme is defined, it must satisfy the requirement of an URL
        if (is_string(parse_url($string, PHP_URL_SCHEME))) {
            return (bool) preg_match(self::URL_REGEX, $string);
        }

        // If provided URL match our popular top domain, we expect it a http(s) URL
        // Thus, it must satisfy the requirement after prefixed with http scheme
        if (preg_match(self::URL_POPULAR_DOMAIN_REGEX, $string)) {
            return is_string(filter_var('http://' . $string, FILTER_VALIDATE_URL));
        }

        return false;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function handleNewLineTag(string $string): string
    {
        // Prepend a blank space before any new line delimeter to prepare for strip tags
        $parsed = preg_replace_callback(self::TEXT_NEW_LINE_REGEX, function ($matches) {
            return MetaFoxConstant::BLANK_SPACE . $matches[0];
        }, $string);

        if (null === $parsed) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return $parsed;
    }

    public function maskedEmail(?string $email, int $left = 3): ?string
    {
        $randomSeed = Str::random(random_int(1, 10));
        $parts      = explode('@', $email);

        if (empty($parts)) {
            return null;
        }

        $parts = collect($parts)
            ->map(function ($part, $key) use ($randomSeed, $left) {
                if ($key === 0) {
                    return Str::mask($part . $randomSeed, '*', $left);
                }

                return '****.***';
            })
            ->values()
            ->toArray();

        return implode('@', $parts);
    }

    public function convertResourceHashtagsToLink(string $content, array $resourceHashtags): string
    {
        if (!count($resourceHashtags)) {
            return $content;
        }

        return preg_replace_callback(sprintf('/%s/', self::HASHTAG_REGEX), function ($matches) use ($resourceHashtags) {
            $search = array_shift($matches);

            $hashtagCharacter = substr($search, 0, 1);

            $hashtag = Str::lower(ltrim($search, implode('', self::NUMBER_SIGN_CHARACTERS)));

            $tagUrl = Arr::get($resourceHashtags, $hashtag);

            if (is_string($tagUrl)) {
                return $this->buildHashtagLink($search, $tagUrl);
            }

            $hashtag = $this->normalizeHashtag(sprintf('#%s', $hashtag), self::HASHTAG_REGEX, self::NUMBER_SIGN_CHARACTERS);

            if (!is_string($hashtag)) {
                return $search;
            }

            $tagUrl = Arr::get($resourceHashtags, $hashtag);

            if (!is_string($tagUrl)) {
                return $search;
            }

            $originalText = $this->normalizeHashtag($search, self::HASHTAG_REGEX, self::NUMBER_SIGN_CHARACTERS);

            $hashtag = sprintf('%s%s', $hashtagCharacter, $originalText);

            $link = $this->buildHashtagLink($hashtag, $tagUrl);

            return str_replace($hashtag, $link, $search);
        }, $content);
    }
}
