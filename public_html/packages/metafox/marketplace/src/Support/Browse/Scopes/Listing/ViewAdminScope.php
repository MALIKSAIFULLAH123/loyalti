<?php

namespace MetaFox\Marketplace\Support\Browse\Scopes\Listing;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewAdminScope.
 *
 * @ignore
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_EXPIRE   = 'expire';
    public const VIEW_ALIVE    = 'alive';
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
                'value' => self::VIEW_ALIVE,
                'label' => __p('marketplace::phrase.alive'),
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
                'value' => self::VIEW_EXPIRE,
                'label' => __p('marketplace::phrase.expired'),
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

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where('marketplace_listings.is_approved', '=', 0);
                break;
            case self::VIEW_APPROVED:
                $builder->where('marketplace_listings.is_approved', '=', 1);
                break;
            case self::VIEW_EXPIRE:
                $this->buildExpired($builder);
                break;
            case self::VIEW_ALIVE:
                $this->buildAlive($builder);
                break;
            case Browse::VIEW_FEATURE:
                $builder->where('marketplace_listings.is_featured', '=', 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where('marketplace_listings.is_sponsor', '=', 1);
                break;
        }
    }

    protected function buildExpired(BuilderContract $builder): void
    {
        $builder->where(function (BuilderContract $builder) {
            $builder->where('marketplace_listings.start_expired_at', '<=', Carbon::now()->timestamp)
                ->where('marketplace_listings.start_expired_at', '>', 0);
        });
    }

    protected function buildAlive(BuilderContract $builder): void
    {
        $builder->where(function (BuilderContract $builder) {
            $builder->where('marketplace_listings.start_expired_at', '>', Carbon::now()->timestamp)
                ->orWhere('marketplace_listings.start_expired_at', '=', 0);
        });
    }
}
