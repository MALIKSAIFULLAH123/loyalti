<?php

namespace MetaFox\Quiz\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\PlayedResult;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Models\ResultDetail;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class ResultRepository.
 * @property Result $model
 * @method   Result getModel()
 */
class ResultRepository extends AbstractRepository implements ResultRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return Result::class;
    }

    private function getQuizRepository(): QuizRepositoryInterface
    {
        return resolve(QuizRepositoryInterface::class);
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @return Quiz
     * @throws AuthorizationException
     */
    public function createResult(User $context, array $attributes): Quiz
    {
        /** @var Quiz $quiz */
        $quiz = $this->getQuizRepository()
            ->with(['questions', 'questions.answers', 'quizText', 'results', 'results.items'])
            ->find($attributes['quiz_id']);

        policy_authorize(QuizPolicy::class, 'play', $context, $quiz);

        $resultItems  = [];
        $totalCorrect = 0;

        $answers = Answer::query()->whereIn('id', array_values($attributes['answers']))->get()->keyBy('id')->toArray();
        foreach ($attributes['answers'] as $questionId => $answerId) {
            $isCorrect = 0;
            if (array_key_exists($answerId, $answers)) {
                $isCorrect    = $answers[$answerId]['is_correct'];
                $totalCorrect = $isCorrect ? ++$totalCorrect : $totalCorrect;
            }

            $resultItems[] = new ResultDetail([
                'question_id' => $questionId,
                'answer_id'   => $answerId,
                'is_correct'  => $isCorrect,
            ]);
        }
        Answer::query()->whereIn('id', array_values($attributes['answers']))->increment('total_play');

        if (!empty($resultItems)) {
            $result = $this->getModel()->fill([
                'user_id'       => $context->entityId(),
                'user_type'     => $context->entityType(),
                'total_correct' => $totalCorrect,
                'quiz_id'       => $quiz->entityId(),
            ]);
            $result->save();
            $result->items()->saveMany($resultItems);
        }

        $quiz->refresh();

        return $quiz;
    }

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewResults(User $context, array $attributes): Paginator
    {
        /** @var Quiz $quiz */
        $quiz = $this->getQuizRepository()->find($attributes['quiz_id']);

        policy_authorize(QuizPolicy::class, 'view', $context, $quiz);

        return $this->getModel()->newModelInstance()->newQuery()
            ->with('items')
            ->where('quiz_id', $quiz->entityId())
            ->simplePaginate($attributes['limit']);
    }

    public function createPlayResult(int $quizId, int $userId): void
    {
        $data = [
            'quiz_id' => $quizId,
            'user_id' => $userId,
        ];
        $playResult = PlayedResult::query()->getModel()->fill($data);
        $playResult->save();
    }

    public function getPlayResult(int $quizId, int $userId)
    {
        return PlayedResult::query()->getModel()
            ->where('quiz_id', '=', $quizId)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function deletePlayResult(int $quizId): void
    {
        PlayedResult::query()->getModel()
            ->where('quiz_id', '=', $quizId)
            ->delete();
    }

    public function viewResult(User $context, array $attributes)
    {
        $quizId = $attributes['quiz_id'];
        $userId = $attributes['user_id'];

        $quiz = resolve(QuizRepositoryInterface::class)->find($quizId);
        policy_authorize(QuizPolicy::class, 'viewMemberResult', $context, $quiz);

        return Result::query()->getModel()
            ->where([
                'quiz_id' => $quizId,
                'user_id' => $userId,
            ])
            ->first();
    }
}
