<?php

namespace MetaFox\Quiz\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuestionRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class QuestionRepository extends AbstractRepository implements QuestionRepositoryInterface
{
    public function model()
    {
        return Question::class;
    }

    /**
     * @param  User                      $context
     * @param  array<string, mixed>      $attributes
     * @return Builder|Model|object|null
     */
    public function viewQuestion(User $context, array $attributes)
    {
        $questionId = $attributes['question_id'];

        /** @var Question $question */
        $question = $this->getModel()->newQuery()
            ->with(['answers', 'quiz'])
            ->where('id', '=', $questionId)
            ->first();

        $quiz     = $question->quiz;

        policy_authorize(QuizPolicy::class, 'view', $context, $quiz);

        return $question;
    }
}
