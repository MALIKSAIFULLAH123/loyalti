<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;

class FeedComposerEditListener
{
    /** @var PollRepositoryInterface */
    private PollRepositoryInterface $repository;

    public function __construct(PollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param User                 $user
     * @param User                 $owner
     * @param mixed                $item
     * @param array<string, mixed> $params
     *
     * @return null|array
     * @throws AuthorizationException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(User $user, User $owner, mixed $item, array $params): ?array
    {
        if ($item?->entityType() != Poll::ENTITY_TYPE) {
            return null;
        }

        if (!$item instanceof Poll) {
            throw new ModelNotFoundException();
        }

        $isSpam = app('events')->dispatch('activity.check_spam_status', [$user, $item->entityType(), $params['content'], $item->entityId()], true);

        if ($isSpam) {
            return [
                'error_message' => __p('core::phrase.you_have_already_added_this_recently_try_adding_something_else'),
            ];
        }

        $privacy = Arr::get($params, 'privacy', $item->privacy);

        $pollParams = [
            'privacy'            => $privacy,
            'caption'            => Arr::get($params, 'content', ''),
            'location_name'      => $params['location_name'] ?? null,
            'location_latitude'  => $params['location_latitude'] ?? null,
            'location_longitude' => $params['location_longitude'] ?? null,
            'location_address'   => $params['location_address'] ?? null,
        ];

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $item->loadPrivacyListAttribute();

            Arr::set($pollParams, 'list', Arr::get($params, 'list', $item->getPrivacyListAttribute()));
        }

        $item->fill($pollParams);

        $item->save();

        $oldPhrase = null;

        if (Arr::get($params, 'is_first_history')) {
            $oldPhrase = 'poll::phrase.added_poll';
        }

        return [
            'success' => true,
            'phrase'  => [
                'old' => $oldPhrase,
                'new' => null,
            ],
            'extra'   => [
                'old' => [],
                'new' => [],
            ],
        ];
    }
}
