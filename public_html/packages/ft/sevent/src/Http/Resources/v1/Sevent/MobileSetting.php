<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;
use MetaFox\Platform\Resource\MobileSetting as Setting;
use MetaFox\Platform\Support\Browse\Browse;

/**
 *--------------------------------------------------------------------------
 * Sevent Mobile Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('sevent')
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'tag'         => ['truthy', 'tag'], 'category_id' => ['truthy', 'category_id'],
                'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'is_featured' => ['truthy', 'is_featured'],
                'view'        => [
                    'includes', 'view', [
                        Browse::VIEW_MY,
                        Browse::VIEW_FRIEND,
                        Browse::VIEW_PENDING,
                        Browse::VIEW_FEATURE,
                        Browse::VIEW_SPONSOR,
                        ViewScope::VIEW_DRAFT,
                        Browse::VIEW_SEARCH,
                        Browse::VIEW_MY_PENDING,
                    ],
                ],
            ])
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
                'view'        => ':view',
            ]);

        $this->add('viewMySevents')
            ->apiUrl('sevent')
            ->apiParams(['view' => 'my']);

        $this->add('viewFriendSevents')
            ->apiUrl('sevent')
            ->apiParams(['view' => 'friend']);

        $this->add('viewDraftSevents')
            ->apiUrl('sevent')
            ->apiParams(['view' => 'draft']);

        $this->add('viewPendingSevents')
            ->apiUrl('sevent')
            ->apiParams(['view' => 'pending']);

        $this->add('viewMyPendingSevents')
            ->apiUrl('sevent')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('viewOnOwner')
            ->apiUrl('sevent')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('viewItem')
            ->apiUrl('sevent/:id')
            ->urlParams(['id' => ':id']);

        $this->add('deleteItem')
            ->apiUrl('sevent/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('sevent::phrase.delete_confirm'),
                ]
            );

        $this->add('editItem')
            ->apiUrl('core/mobile/form/sevent.sevent.update/:id');

        $this->add('editFeedItem')
            ->apiUrl('core/mobile/form/sevent.sevent.update/:id');

        $this->add('addItem')
            ->apiUrl('core/mobile/form/sevent.sevent.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('publishSevent')
            ->apiUrl('sevent/publish/:id')
            ->asPatch()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('sevent::phrase.publish_sevent_confirm'),
            ]);

        $this->add('approveItem')
            ->apiUrl('sevent/approve/:id')
            ->asPatch();

        $this->add('sponsorItem')
            ->apiUrl('sevent/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('sevent/sponsor-in-feed/:id')
            ->asPatch();

        $this->add('featureItem')
            ->apiUrl('sevent/feature/:id');

        $this->add('searchItem')
            ->apiUrl('sevent')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'category_id' => ':category_id',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('sevent::phrase.search_sevents'));

        $this->add('searchGlobalSevent')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'sevent',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('sevent')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('sevent::phrase.search_sevents'));

        $this->add('sponsorItem')
            ->apiUrl('sevent/sponsor/:id');
    }
}
