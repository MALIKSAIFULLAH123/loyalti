<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;

class CreateThreadIntegrationListener
{
    /** @var PollRepository */
    private $repository;

    public function __construct(PollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                                           $user
     * @param  User                                           $owner
     * @param  string                                         $entityType
     * @param  array                                          $params
     * @return int|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user, User $owner, string $entityType, array $params): ?int
    {
        if ($entityType != Poll::ENTITY_TYPE) {
            return null;
        }

        $params = $this->repository->prepareDataForFeed($params);

        $closeTime = null;

        if (Arr::has($params, 'closed_at')) {
            $closeTime = Arr::get($params, 'closed_at');
        }

        $pollParams = array_merge($params, [
            'text'    => '',
            'view_id' => PollFacade::getIntegrationViewId(),
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'closed_at' => $closeTime,
            'force_approved' => true,
        ]);

        unset($pollParams['content']);

        $poll = $this->repository->createPoll($user, $owner, $pollParams);

        if (null !== $poll) {
            return $poll->entityId();
        }

        return null;
    }
}
