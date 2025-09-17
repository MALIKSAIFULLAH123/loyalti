<?php

namespace MetaFox\App\Support\Browse\Scopes\Package;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class TypeScope.
 * @ignore
 * @codeCoverageIgnore
 */
class TypeScope extends BaseScope
{
    public const VIEW_APP      = 'app';
    public const VIEW_LANGUAGE = 'language';
    public const VIEW_THEME = 'theme';

    /**
     * @var string
     */
    protected string $type;

    /**
     * Get the value of type.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the value of type.
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_ALL_DEFAULT,
            self::VIEW_APP,
            self::VIEW_LANGUAGE,
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function getAllowOptions(): array
    {
        return [
            [
                'value' => self::VIEW_APP,
                'label' => __p('app::phrase.app'),
            ],
            [
                'value' => self::VIEW_LANGUAGE,
                'label' => __p('app::phrase.language'),
            ],
            [
                'value' => self::VIEW_THEME,
                'label' => __p('app::phrase.theme'),
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $type = $this->getType();
        if (empty($type)) {
            return;
        }

        $builder->where('type', $type);
    }
}
