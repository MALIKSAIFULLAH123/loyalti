<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Answers;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Models\QuestionField;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class QuestionRepository.
 * @method Question getModel()
 * @method Question find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @ignore
 */
class QuestionRepository extends AbstractRepository implements QuestionRepositoryInterface
{
    public function model(): string
    {
        return Question::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getQuestions(User $context, array $attributes): Paginator
    {
        $groupId = $attributes['group_id'];

        $group = $this->groupRepository()
            ->with(['groupQuestions'])
            ->find($groupId);

        $limit = Arr::get($attributes, 'limit');

        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $group);

        if (null === $limit) {
            $limit = $group->groupQuestions->count();
        }

        return $this->getModel()->newQuery()
            ->with(['questionFields'])
            ->where('group_id', $groupId)
            ->orderBy('id')
            ->simplePaginate($limit);
    }

    public function getQuestionsForForm(int $groupId): ?Collection
    {
        return $this->getModel()->newQuery()
            ->with(['questionFields'])
            ->where('group_id', $groupId)
            ->get();
    }

    public function createQuestion(User $context, array $attributes): Question
    {
        $groupId = $attributes['group_id'];

        $group = $this->groupRepository()->find($groupId);

        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $group);

        $checkCount = $this->getModel()->newQuery()
            ->where('group_id', $groupId)
            ->count();

        $maxQuestionSetting = Settings::get('group.maximum_membership_question', Question::MAX_QUESTION);

        if ($maxQuestionSetting == $checkCount) {
            abort(403, __p('group::phrase.you_can_only_add_up_to_number_questions', ['max' => $maxQuestionSetting]));
        }

        /** @var Question $question */
        $question = parent::create($attributes);

        $this->addQuestionFields($question, $attributes);

        return $question->loadMissing(['questionFields']);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateQuestion(User $context, int $id, array $attributes): Question
    {
        $question = $this->with(['group', 'questionFields'])->find($id);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $question->group);

        $oldTypeId = $question->type_id;
        $question->fill($attributes);
        $question->save();

        if ($question->type_id == Question::TYPE_TEXT) {
            if ($oldTypeId != Question::TYPE_TEXT) {
                $this->deleteRelationsOfQuestion($question);
            }
        }

        if ($question->type_id != Question::TYPE_TEXT) {
            if ($oldTypeId == Question::TYPE_TEXT) {
                if (empty($attributes['options']['new'])) {
                    throw ValidationException::withMessages([__p('group::phrase.please_provide_options')]);
                }

                $this->deleteRelationsOfQuestion($question);
            }

            if (!empty($attributes['options'])) {
                $this->addQuestionFields($question, $attributes);

                if (!empty($attributes['options']['update'])) {
                    foreach ($attributes['options']['update'] as $option) {
                        QuestionField::query()->where('id', $option['id'])->update(['title' => $option['title']]);
                    }
                }

                if (!empty($attributes['options']['remove'])) {
                    $removeOptions = Arr::get($attributes, 'options.remove', []);
                    $removeIds     = collect($removeOptions)->pluck('id')->toArray();
                    QuestionField::query()->whereIn('id', $removeIds)->delete();
                }
            }
        }

        return $question->refresh();
    }

    public function deleteRelationsOfQuestion(Question $question): Question
    {
        $question->questionFields()->delete();
        $question->answers()->delete();

        return $question->refresh();
    }

    public function deleteQuestion(User $context, int $id): bool
    {
        $question = $this->with(['group'])->find($id);
        policy_authorize(GroupPolicy::class, 'manageGroup', $context, $question->group);

        return (bool) $question->delete();
    }

    public function createAnswer(User $context, array $attributes, int $requestId): bool
    {
        foreach ($attributes as $key => $value) {
            $questionParams = explode('_', $key);

            if (!is_array($questionParams)
                || count($questionParams) == 0
                || $questionParams[0] != 'question') {
                return false;
            }

            $question = $this->find($questionParams[1]);

            $answerData = [
                'user_id'     => $context->entityId(),
                'user_type'   => $context->entityType(),
                'question_id' => $question->entityId(),
                'request_id'  => $requestId,
            ];

            if (in_array($question->type_id, [Question::TYPE_TEXT, Question::TYPE_SELECT])) {
                $answerData['value'] = $value;

                if ($question->type_id == Question::TYPE_SELECT) {
                    $answerData['value'] = (int) $value;
                }

                (new Answers($answerData))->save();
            }

            if ($question->type_id == Question::TYPE_MULTI_SELECT) {
                $answersData = [];
                foreach ($value as $item) {
                    $answerData['value'] = (int) $item;
                    $answersData[]       = $answerData;
                }

                if (!empty($answersData)) {
                    Answers::query()->insert($answersData);
                }
            }
        }

        return true;
    }

    /**
     * @param  Question             $question
     * @param  array<string, mixed> $attributes
     * @return bool
     */
    protected function addQuestionFields(Question $question, array $attributes = []): bool
    {
        if ($question->type_id === Question::TYPE_TEXT) {
            return false;
        }

        $newOptions = Arr::get($attributes, 'options.new', []);
        foreach ($newOptions as $option) {
            QuestionField::query()->create([
                'title'       => Arr::get($option, 'title', ''),
                'question_id' => $question->entityId(),
            ]);
        }

        return true;
    }

    /**
     * @param  User                   $context
     * @param  Request                $request
     * @return Collection
     * @throws AuthorizationException
     */
    public function getAnswersByRequestId(User $context, Request $request): Collection
    {
        $group     = $this->groupRepository()->find($request->group_id);
        $relations = [
            'answers' => fn (HasMany $query) => $query->where('request_id', $request->entityId()),
        ];

        policy_authorize(GroupPolicy::class, 'managePendingRequestTab', $context, $group);

        return $this->getModel()->newQuery()
            ->with($relations)
            ->where('group_id', $request->group_id)
            ->get();
    }
}
