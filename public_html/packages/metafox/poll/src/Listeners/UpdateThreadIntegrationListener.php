<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;

class UpdateThreadIntegrationListener
{
    /** @var PollRepository */
    private $repository;

    public function __construct(PollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                                           $user
     * @param  string                                         $entityType
     * @param  int                                            $entityId
     * @param  array                                          $params
     * @return bool|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user, string $entityType, int $entityId, array $params): ?bool
    {
        if ($entityType != Poll::ENTITY_TYPE) {
            return null;
        }

        $answers = null;

        if (Arr::has($params, 'poll_answers')) {
            $ordering = 0;

            $formattedAnswer = [];

            foreach ($params['poll_answers'] as $answer) {
                // Reset the ordering
                $answer['ordering'] = ++$ordering;

                if (Arr::has($answer, 'id')) {
                    $formattedAnswer['changedAnswers'][$answer['id']] = $answer;
                    continue;
                }

                $formattedAnswer['newAnswers'][] = $answer;
            }

            $answers = $formattedAnswer;
        }

        $params = $this->repository->prepareDataForFeed($params);

        $params['answers'] = $answers;

        $closeTime = null;

        if (Arr::has($params, 'closed_at')) {
            $closeTime = Arr::get($params, 'closed_at');
        }

        $pollParams = array_merge($params, [
            'closed_at' => $closeTime,
        ]);

        $this->repository->updatePoll($user, $entityId, $pollParams);

        return true;
    }
}
