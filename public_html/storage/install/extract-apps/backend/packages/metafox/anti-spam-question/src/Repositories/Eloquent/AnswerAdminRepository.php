<?php

namespace MetaFox\AntiSpamQuestion\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\AntiSpamQuestion\Models\Answer;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\AntiSpamQuestion\Repositories\AnswerAdminRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Models\Option;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class AnswerAdminRepository
 *
 */
class AnswerAdminRepository extends AbstractRepository implements AnswerAdminRepositoryInterface
{
    public function model()
    {
        return Answer::class;
    }

    public function createAnswer(Question $question, array $attributes): void
    {
        foreach ($attributes as $ordering => $attribute) {
            /** @var Option $model */
            $model = $this->getModel()->newInstance();

            $model->fill([
                'answer'      => Arr::get($attribute, 'value'),
                'question_id' => $question->entityId(),
                'ordering'    => Arr::get($attribute, 'ordering', $ordering),
            ]);

            $model->save();
        }
    }

    public function updateAnswer(Question $question, array $attributes): void
    {
        foreach ($attributes as $ordering => $attribute) {
            $optionId = Arr::get($attribute, 'id');

            /** @var Answer $model */
            $model = $this->find($optionId);
            $model->fill([
                'answer'   => Arr::get($attribute, 'value'),
                'ordering' => Arr::get($attribute, 'ordering', $ordering),
            ]);

            $model->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function removeAnswers(Question $question, array $attributes): void
    {
        $removeIds = collect($attributes)->pluck('id')->toArray();
        $question->answers()->whereIn('id', $removeIds)->delete();
    }
}
