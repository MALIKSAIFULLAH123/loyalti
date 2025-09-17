<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Album;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewAdminScope.
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT = Browse::VIEW_ALL;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return Arr::pluck(self::getViewOptions(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function getViewOptions(): array
    {
        return [
            [
                'value' => Browse::VIEW_ALL,
                'label' => __p('core::phrase.all'),
            ],
            [
                'value' => Browse::VIEW_FEATURE,
                'label' => __p('core::phrase.featured'),
            ],
            [
                'value' => Browse::VIEW_SPONSOR,
                'label' => __p('core::web.sponsored'),
            ],
        ];
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    protected User $user;


    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ViewScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
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

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        $view  = $this->getView();

        switch ($view) {
            case Browse::VIEW_FEATURE:
                $builder->where("$table.is_featured", 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where("$table.is_sponsor", 1);
                break;
        }
    }
}
