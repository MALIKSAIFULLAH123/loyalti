<?php

namespace MetaFox\Core\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Ban\Repositories\Eloquent\BanRuleRepository;
use MetaFox\Ban\Supports\Constants;
use MetaFox\Platform\Facades\Settings;

class BanWord implements \MetaFox\Platform\Contracts\BanWord
{
    public const START_OF_STRING_WILDCARD_PATTERN = '(?:(?<=^)|(?<=^)[\p{L}\p{M}\p{N}_]+|(?<=[^\p{L}\p{M}\p{N}\p{S}_])|(?<=[^\p{L}\p{M}\p{N}\p{S}_])[\p{L}\p{M}\p{N}_]+)';
    public const END_OF_STRING_WILDCARD_PATTERN   = '(?:(?=$)|[\p{L}\p{M}\p{N}_]+(?=$)|[\p{L}\p{M}\p{N}_]+(?=[^\p{L}\p{M}\p{N}\p{S}_])|(?=[^\p{L}\p{M}\p{N}\p{S}_]))';
    public const START_OF_STRING_PATTERN          = '(?:(?<=[^\p{L}\p{M}\p{N}\p{S}_])|(?<=^))';
    public const END_OF_STRING_PATTERN            = '(?:(?=[^\p{L}\p{M}\p{N}\p{S}_])|(?=$))';
    public const MIDDLE_OF_STRING_PATTERN         = '(?:[\p{L}\p{M}\p{N}_]*)';
    public const ALL_WORDS_IN_STRING_PATTERN      = self::START_OF_STRING_PATTERN . '(?:[\p{L}\p{M}\p{N}_]+)' . self::END_OF_STRING_PATTERN;

    /**
     * @var array<mixed>
     */
    private array $words = [];

    /**
     * @var array
     */
    private array $regexBannedWords = [];

    public function __construct(protected BanRuleRepository $banRuleRepository)
    {
        $this->init();
    }

    private function init(): void
    {
        $this->words            = $this->getWordsFormBanRule();
        $this->regexBannedWords = $this->prepareRegexBannedWords();
    }

    protected function prepareRegexBannedWords(): array
    {
        return localCacheStore()->remember('ban::word::prepareRegexBannedWords', 3600, function () {
            $words = $this->getWordsFormBanRule();

            if (!count($words)) {
                return [];
            }

            $transforms = [];

            foreach ($words as $findValue => $replacement) {
                if (null === $findValue) {
                    continue;
                }

                $findValue = (string) $findValue;

                $findValue = str_replace(['/', '+', '.', '?', '^', '$'], ["\/", "\+", "\.", "\?", "\^", '$'], $findValue);

                $findValue = str_replace('&#42;', '*', $findValue);

                if ($this->isAllowHtml()) {
                    $findValue = str_replace('&#039;', '\'', $findValue);
                }

                if ('' === $findValue) {
                    continue;
                }

                Arr::set($transforms, $findValue, [
                    'replacement' => $replacement,
                    'find_regex'  => $this->transformRegexPattern($findValue),
                ]);
            }

            return $transforms;
        });
    }

    protected function getWordsFormBanRule(): array
    {
        return Cache::remember(
            'core::getWordsFormBanRule()',
            3600,
            function () {
                $rules = $this->banRuleRepository->getBanRulesByType(Constants::BAN_WORD_TYPE);

                return $rules->pluck('replacement', 'find_value')->toArray();
            }
        );
    }

    /**
     * @return array<mixed>
     */
    public function getWords(): array
    {
        return $this->words;
    }

    public function clean(?string $string = null): ?string
    {
        if (!is_string($string)) {
            return $string;
        }

        return $this->parseString($string);
    }

    public function hasBanWord(?string $string): bool
    {
        if (null === $string) {
            return false;
        }

        if (!count($this->regexBannedWords)) {
            return false;
        }

        foreach ($this->regexBannedWords as $mValue) {
            $regex = Arr::get($mValue, 'find_regex');

            if (!is_array($regex)) {
                continue;
            }

            $pattern = Arr::get($regex, 'pattern');
            $flag    = Arr::get($regex, 'flag');

            if (preg_match(sprintf('/%s/%s', $pattern, $flag), $string)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $string
     *
     * @return string
     * @SuppressWarnings(PHPMD)
     * @todo implement later
     */
    private function parseString(?string $string = null): string
    {
        if (null === $string) {
            return '';
        }

        if (!count($this->regexBannedWords)) {
            return $string;
        }

        foreach ($this->regexBannedWords as $mValue) {
            $regex = Arr::get($mValue, 'find_regex');

            $replacement = Arr::get($mValue, 'replacement');

            if (!is_array($regex) || !is_string($replacement)) {
                continue;
            }

            $pattern = Arr::get($regex, 'pattern');

            $flag = Arr::get($regex, 'flag');

            $string = preg_replace(sprintf('/%s/%s', $pattern, $flag), $replacement, $string);

            $string = trim($string);
        }

        if (!is_string($string)) {
            return '';
        }

        return $string;
    }

    public function transformRegexPattern(?string $findValue): ?array
    {
        if (null === $findValue) {
            return null;
        }

        if (preg_match('/^[*]+$/', $findValue)) {
            return [
                'pattern' => self::ALL_WORDS_IN_STRING_PATTERN,
                'flag'    => 'uism',
            ];
        }

        $findValue = preg_replace('/([^*])([*]+)([^*])/', sprintf('$1%s$3', self::MIDDLE_OF_STRING_PATTERN), $findValue);

        if (preg_match('/^(\*+)([^*]+)/', $findValue)) {
            $findValue = preg_replace('/^(\*+)([^*]+)/', sprintf('%s$2', self::START_OF_STRING_WILDCARD_PATTERN), $findValue);
        } else {
            $findValue = sprintf('%s%s', self::START_OF_STRING_PATTERN, $findValue);
        }

        if (preg_match('/([^*]+)(\*+)$/', $findValue)) {
            $findValue = preg_replace('/([^*]+)(\*+)$/', sprintf('$1%s', self::END_OF_STRING_WILDCARD_PATTERN), $findValue);
        } else {
            $findValue = sprintf('%s%s', $findValue, self::END_OF_STRING_PATTERN);
        }

        return [
            'pattern' => $findValue,
            'flag'    => 'uism',
        ];
    }

    private function isAllowHtml(): bool
    {
        return Settings::get('core.general.allow_html', false);
    }

    /**
     * @param  string|null $string
     * @return string
     */
    public function parse(?string $string = null): string
    {
        // TODO: Implement parse() method.
        if (!is_string($string)) {
            return '';
        }

        return $string;
    }
}
