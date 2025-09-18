<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;

class FeedComposerListener
{
    /** @var PollRepositoryInterface */
    private PollRepositoryInterface $repository;

    public function __construct(PollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                                           $user
     * @param  User                                           $owner
     * @param  string                                         $postType
     * @param  array                                          $params
     * @return array|int[]|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user, User $owner, string $postType, array $params): ?array
    {
        if ($postType != Poll::FEED_POST_TYPE) {
            return null;
        }

        if (false === policy_check(PollPolicy::class, 'hasCreateFeed', $owner, $postType)) {
            return [
                'error_message' => __('validation.no_permission'),
            ];
        }

        $content = Arr::get($params, 'content', '');

        unset($params['content']);

        $pollParams = array_merge($params, [
            'text'                   => '',
            'caption'                => $content,
            'pending_tagged_friends' => Arr::get($params, 'tagged_friends'),
        ]);

        $poll = $this->repository->createPoll($user, $owner, $pollParams);

        LoadReduce::flush();

        $poll->load('activity_feed');

        $data = [
            'id' => $poll->activity_feed ? $poll->activity_feed->entityId() : 0,
        ];

        if ($data['id'] == 0 && !$poll->isApproved()) {
            $data = array_merge($data, [
                'is_pending' => true,
                'message'    => $poll->getOwnerPendingMessage(),
            ]);
        }

        return $data;
    }
}
