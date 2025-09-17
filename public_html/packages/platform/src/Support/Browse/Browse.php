<?php

namespace MetaFox\Platform\Support\Browse;

use MetaFox\Platform\Support\Browse\Contracts\BrowseInterface;

/**
 * Class Browse.
 */
class Browse implements BrowseInterface
{
    /**
     * @deprecated
     */
    public const SORT_LATEST = 'latest';

    public const SORT_RECENT         = 'recent';
    public const SORT_MOST_VIEWED    = 'most_viewed';
    public const SORT_MOST_LIKED     = 'most_liked';
    public const SORT_MOST_DISCUSSED = 'most_discussed';
    public const SORT_A_TO_Z         = 'a_to_z';
    public const SORT_Z_TO_A         = 'z_to_a';
    public const SORT_FEATURE        = 'feature';

    public const SORT_TYPE_DESC = 'desc';
    public const SORT_TYPE_ASC  = 'asc';

    public const VIEW_ALL_DEFAULT  = '';
    public const VIEW_ALL          = 'all';
    public const VIEW_MY           = 'my';
    public const VIEW_FRIEND       = 'friend';
    public const VIEW_PENDING      = 'pending';
    public const VIEW_FEATURE      = 'feature';
    public const VIEW_SPONSOR      = 'sponsor';
    public const VIEW_MY_PENDING   = 'my_pending';
    public const VIEW_LATEST       = 'latest';
    public const VIEW_SEARCH       = 'search';
    public const VIEW_SIMILAR      = 'similar';
    public const VIEW_YOUR_CONTENT = 'your_content';

    public const WHEN_ALL        = 'all';
    public const WHEN_THIS_MONTH = 'this_month';
    public const WHEN_THIS_WEEK  = 'this_week';
    public const WHEN_TODAY      = 'today';
    public const WHEN_BETWEEN    = 'between';

    public function getSortFilters(): array
    {
        return [
            self::SORT_RECENT         => 'recent',
            self::SORT_MOST_VIEWED    => 'most_viewed',
            self::SORT_MOST_LIKED     => 'most_liked',
            self::SORT_MOST_DISCUSSED => 'most_discussed',
        ];
    }

    public function getViewFilters(): array
    {
        return [
            self::VIEW_ALL     => 'all_listings',
            self::VIEW_MY      => 'my_listings',
            self::VIEW_FRIEND  => 'friends_listings',
            self::VIEW_PENDING => 'pending_listings',
        ];
    }

    public function getWhenFilters(): array
    {
        return [
            self::WHEN_ALL        => 'all_listings',
            self::WHEN_THIS_MONTH => 'this_month',
            self::WHEN_THIS_WEEK  => 'this_week',
            self::WHEN_TODAY      => 'today',
        ];
    }

    public function getListFilter(): array
    {
        return [
            'sort' => $this->getSortFilters(),
            'view' => $this->getSortFilters(),
            'when' => $this->getSortFilters(),
        ];
    }
}
