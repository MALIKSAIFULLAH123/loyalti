<?php

namespace MetaFox\Page\Support\Browse\Scopes\Page;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewAdminScope.
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_APPROVED = 'approved';

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
                'value' => self::VIEW_APPROVED,
                'label' => __p('core::phrase.approved'),
            ],
            [
                'value' => Browse::VIEW_PENDING,
                'label' => __p('core::phrase.pending'),
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
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    private User $userContext;

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->userContext;
    }

    /**
     * @param User $userContext
     *
     * @return ViewScope
     */
    public function setUserContext(User $userContext): self
    {
        $this->userContext = $userContext;

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

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        switch ($view) {
            case Browse::VIEW_FEATURE:
                $builder->where('pages.is_featured', '=', 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where('pages.is_sponsor', '=', 1);
                break;
            case Browse::VIEW_PENDING:
                $builder->whereNot('pages.is_approved', Page::IS_APPROVED);
                break;
            case self::VIEW_APPROVED:
                $builder->where('pages.is_approved', Page::IS_APPROVED);
        }
    }
}
