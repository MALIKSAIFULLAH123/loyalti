<?php

namespace MetaFox\Poll\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class ResultRepository.
 *
 * @method Result getModel()
 */
class ResultRepository extends AbstractRepository implements ResultRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return Result::class;
    }

    public function getPollRepository(): PollRepositoryInterface
    {
        return resolve(PollRepositoryInterface::class);
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewResults(User $context, array $attributes): Paginator
    {
        $limit = $attributes['limit'];
        if (isset($attributes['answer_id'])) {
            return $this->viewResultsByAnswer($context, $attributes['answer_id'], $limit);
        }

        /** @var Poll $poll */
        $poll = $this->getPollRepository()->with(['results', 'owner'])->find($attributes['poll_id']);

        policy_authorize(PollPolicy::class, 'view', $context, $poll);

        return $this->getModel()->newModelQuery()
            ->with(['user', 'answer'])
            ->where('poll_id', $poll->entityId())
            ->orderByDesc('id')
            ->simplePaginate($limit);
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @return Poll
     * @throws AuthorizationException
     * @throws Exception
     */
    public function createResult(User $context, array $attributes): Poll
    {
        /** @var Poll $poll */
        $poll = $this->getPollRepository()->find($attributes['poll_id']);

        policy_authorize(PollPolicy::class, 'voteOwn', $context, $poll);
        $this->checkVoteAgain($context, $poll);

        $this->processCreateResult($context, $poll, $attributes['answers']);

        $poll->save();

        $poll->refresh();

        return $poll;
    }

    private function checkVoteAgain(User $context, ?Poll $poll): void
    {
        if (policy_check(PollPolicy::class, 'voteAgain', $context, $poll)) {
            return;
        }

        abort(403, __p('poll::phrase.you_have_already_completed_this_poll'));
    }

    /**
     * @param  User                   $context
     * @param  int                    $id
     * @param  array<string, mixed>   $attributes
     * @return Poll
     * @throws AuthorizationException
     */
    public function updateResult(User $context, int $id, array $attributes): Poll
    {
        $poll = $this->getPollRepository()->find($id);

        // User must have the permission to view poll
        policy_authorize(PollPolicy::class, 'view', $context, $poll);

        // Reject user by throw an Authorization exception here if he/she cannot change their vote
        $this->shouldAllowVoteAgain($context, $id);

        if (!empty($attributes['answers'])) {
            //Add new result
            $this->processCreateResult($context, $poll, $attributes['answers']);
        }

        $poll->refresh();

        return $poll;
    }

    /**
     * @param  User       $context
     * @param  Poll       $poll
     * @param  array<int> $answers
     * @return Poll
     */
    protected function processCreateResult(User $context, Poll $poll, array $answers): Poll
    {
        //Remove old result if any
        $poll->results()->where('user_id', $context->entityId())->get()->each->delete();

        foreach ($answers as $key => $answerId) {
            /** @var Result $model */
            $model = $this->getModel()->newModelInstance();
            $model->fill([
                'poll_id'   => $poll->entityId(),
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
                'answer_id' => $answerId,
                'is_last'   => $key == (count($answers) - 1),
            ]);
            $model->save();
        }

        $this->updateAnswersPercentage($poll);

        return $poll;
    }

    /**
     * @param Poll $poll
     */
    public function updateAnswersPercentage(Poll $poll): void
    {
        $answers          = $poll->answers()->get();
        $totalAnswerVotes = $answers->sum('total_vote');
        if ($totalAnswerVotes <= 0) {
            $totalAnswerVotes = 1;
        }
        $answers->each(function (Answer $answer) use ($totalAnswerVotes) {
            $percentage = round(($answer->total_vote / $totalAnswerVotes) * 100, 2);
            $answer->update(['percentage' => $percentage]);
        });
    }

    private function viewResultsByAnswer(User $context, int $answerId, int $limit): Paginator
    {
        $poll = $this->getPollRepository()->getPollByAnswerId($context, $answerId);

        $isVoted = $this->getPollRepository()->isUserVoted($context, $poll->entityId());

        match ($isVoted) {
            true  => policy_authorize(PollPolicy::class, 'viewResultAfterVote', $context, $poll),
            false => policy_authorize(PollPolicy::class, 'viewResultBeforeVote', $context, $poll),
        };

        return $this->getModel()->newModelQuery()
            ->with(['user', 'answer'])
            ->where('poll_id', $poll->entityId())
            ->where('answer_id', $answerId)
            ->orderByDesc('id')
            ->simplePaginate($limit);
    }

    /**
     * A check point to verify if user is able to change their own vote.
     * @throws AuthorizationException
     */
    protected function shouldAllowVoteAgain(User $context, int $id): void
    {
        if ($this->getPollRepository()->isUserVoted($context, $id)) {
            policy_authorize(PollPolicy::class, 'changeVote', $context);

            return;
        }

        policy_authorize(PollPolicy::class, 'vote', $context);
    }

    /**
     * @param  array $ids
     * @return void
     */
    public function deletePollResultNotificationByIds(array $ids): void
    {
        app('events')->dispatch(
            'notification.delete_notification_by_items',
            ['poll_notification', $ids, Result::ENTITY_TYPE]
        );
    }
}
