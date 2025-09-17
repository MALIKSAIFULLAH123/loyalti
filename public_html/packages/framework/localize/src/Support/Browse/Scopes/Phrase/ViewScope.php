<?php

namespace MetaFox\Localize\Support\Browse\Scopes\Phrase;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT        = Browse::VIEW_ALL;
    public const VIEW_TRANSLATED     = 'translated';
    public const VIEW_NOT_TRANSLATED = 'not_translated';

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowView())];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        $allowView = [
            Browse::VIEW_ALL,
            self::VIEW_TRANSLATED,
            self::VIEW_NOT_TRANSLATED,
        ];

        return $allowView;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getFilterOptions(): array
    {
        return [
            [
                'label' => __p('localize::phrase.all_phrases_filter'),
                'value' => self::VIEW_DEFAULT,
            ],
            [
                'label' => __p('localize::phrase.translated_filter'),
                'value' => self::VIEW_TRANSLATED,
            ],
            [
                'label' => __p('localize::phrase.not_translated_filter'),
                'value' => self::VIEW_NOT_TRANSLATED,
            ],
        ];
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_DEFAULT;

    /**
     * @return string
     */
    public function getView(): string
    {
        if ('en' === $this->getLocale()) {
            return self::VIEW_DEFAULT;
        }

        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    protected string $locale = 'en';

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view   = $this->getView();
        $locale = $this->getLocale();

        $builder->where('phrases.locale', $locale);

        switch ($view) {
            case self::VIEW_TRANSLATED:
                $builder
                ->join('phrases as derived', function (JoinClause $join) {
                    $join->on('phrases.key', '=', 'derived.key');
                    $join->where('derived.locale', 'en');
                })
                ->where(function (Builder $where) {
                    $where->whereColumn('phrases.text', '<>', 'derived.text')
                        ->orWhere('phrases.text', 'like', '{%}');
                });
                break;
            case self::VIEW_NOT_TRANSLATED:
                $builder
                ->join('phrases as derived', function (JoinClause $join) {
                    $join->on('phrases.key', '=', 'derived.key');
                    $join->where('derived.locale', 'en');
                })
                ->where('phrases.is_modified', '<>', 1)
                ->whereNotNull('phrases.text')
                ->where('phrases.text', '<>', '')
                ->where('phrases.group', '<>', 'translatable')
                ->whereColumn('phrases.text', '=', 'derived.text')
                ->whereNot('phrases.text', 'like', '{%}');
                break;
            default:
                break;
        }
    }
}
