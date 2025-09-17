<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_ONLINE   = 'online';
    public const VIEW_APPROVED = 'approved';
    public const VIEW_ENDED    = 'ended';
    public const VIEW_ONGOING  = 'ongoing';
    public const VIEW_UPCOMING = 'upcoming';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return Arr::pluck(self::getViewOptions(), 'value');
    }

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
            [
                'value' => self::VIEW_ONLINE,
                'label' => __p('event::phrase.online'),
            ],
            [
                'value' => self::VIEW_ENDED,
                'label' => __p('event::phrase.ended'),
            ],
            [
                'value' => self::VIEW_ONGOING,
                'label' => __p('core::web.ongoing'),
            ],
            [
                'value' => self::VIEW_UPCOMING,
                'label' => __p('core::web.upcoming'),
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();
        $date = Carbon::now();

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where('events.is_approved', '!=', 1);
                break;
            case self::VIEW_APPROVED:
                $builder->where('events.is_approved', '=', 1);
                break;
            case self::VIEW_ONLINE:
                $builder->where('events.is_online', '=', 1);
                break;
            case self::VIEW_ENDED:
                $builder->where('events.end_time', '<', $date);
                break;
            case self::VIEW_ONGOING:
                $builder->where('events.end_time', '>=', $date)
                    ->where('events.start_time', '<=', $date);
                break;
            case self::VIEW_UPCOMING:
                $builder->where('events.start_time', '>=', $date);
                break;
            case Browse::VIEW_FEATURE:
                $builder->where('events.is_featured', '=', 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where('events.is_sponsor', '=', 1);
                break;
        }
    }
}
