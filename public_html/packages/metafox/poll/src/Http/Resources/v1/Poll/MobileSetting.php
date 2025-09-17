<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Poll\Http\Resources\v1\Poll;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 *--------------------------------------------------------------------------
 * Poll Mobile Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class PollMobileSetting
 * Inject this class into property $resources.
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @driverType resource-mobile
 * @driverName poll
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('poll')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => 'search',
                'is_featured' => ':is_featured',
            ])
            ->placeholder(__p('poll::phrase.search_polls'));

        $this->add('homePage')
            ->pageUrl('poll');

        $this->add('viewAll')
            ->apiUrl('poll')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'view'        => ':view',
                'is_featured' => ':is_featured',
            ])
            ->apiRules([
                'q'       => [
                    'truthy', 'q',
                ], 'sort' => ['includes', 'sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed']],
                'when'    => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'view'    => ['includes', 'view', ['my', 'friend', 'pending', 'sponsor', 'feature']],
            ]);

        $this->add('viewItem')
            ->apiUrl('poll/:id')
            ->pageUrl('poll/:id');

        $this->add('deleteItem')
            ->apiUrl('poll/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('poll::phrase.delete_confirm'),
                ]
            );

        $this->add('addItem')
            ->pageUrl('poll/add')
            ->apiUrl('core/mobile/form/poll.poll.store')
            ->apiParams(['owner_id' => ':id']);

        $this->add('editItem')
            ->pageUrl('poll/edit/:id')
            ->apiUrl('core/mobile/form/poll.poll.update/:id');

        $this->add('editFeedItem')
            ->pageUrl('poll/edit/:id')
            ->apiUrl('core/mobile/form/poll.poll.update/:id');

        $this->add('sponsorItem')
            ->apiUrl('poll/sponsor/:id');

        $this->add('sponsorItemInFeed')
            ->apiUrl('poll/sponsor-in-feed/:id')
            ->asPatch();

        /**
         * @deprecated Remove in 5.2.0
         */
        $this->add('featureItem')
            ->apiUrl('poll/feature/:id');

        $this->add('featureFreeItem')
            ->asPatch()
            ->apiUrl('poll/feature/:id')
            ->apiParams([
                'feature' => 1,
            ]);

        $this->add('unfeatureItemNew')
            ->asPatch()
            ->apiUrl('poll/feature/:id')
            ->apiParams([
                'feature' => 0,
            ]);

        $this->add('approveItem')
            ->apiUrl('poll/approve/:id')
            ->asPatch();

        $this->add('viewMyPolls')
            ->apiUrl('poll')
            ->apiParams([
                'view' => 'my',
            ]);

        $this->add('viewFriendPolls')
            ->apiUrl('poll')
            ->apiParams([
                'view' => 'friend',
            ]);

        $this->add('viewPendingPolls')
            ->apiUrl('poll')
            ->apiParams([
                'view' => 'pending',
            ]);

        $this->add('viewOnOwner')
            ->apiUrl('poll')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('viewMyPendingPolls')
            ->apiUrl('poll')
            ->apiParams([
                'view' => 'my_pending',
            ]);

        $this->add('votePoll')
            ->apiUrl('poll-result')
            ->asPost();

        $this->add('votePollAgain')
            ->apiUrl('poll-result/:id')
            ->asPatch();

        $this->add('getStatusForm')
            ->apiUrl('core/mobile/form/poll.feed_form');

        $this->add('searchGlobalPoll')
            ->apiUrl(apiUrl('search.index'))
            ->apiParams([
                'view'                        => 'poll',
                'q'                           => ':q',
                'owner_id'                    => ':owner_id',
                'when'                        => ':when',
                'related_comment_friend_only' => ':related_comment_friend_only',
                'is_hashtag'                  => ':is_hashtag',
                'from'                        => ':from',
            ]);

        $this->add('searchInOwner')
            ->apiUrl('poll')
            ->apiParams([
                'q'        => ':q',
                'owner_id' => ':id',
                'view'     => 'search',
            ])
            ->placeholder(__p('poll::phrase.search_polls'));
    }
}
