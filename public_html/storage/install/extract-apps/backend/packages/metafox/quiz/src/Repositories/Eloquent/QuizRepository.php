<?php

namespace MetaFox\Quiz\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Quiz\Jobs\UpdateQuizResultJob;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Models\ResultDetail;
use MetaFox\Quiz\Notifications\QuizResubmitNotifications;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\SortScope;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\ViewScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class QuizRepository.
 * @property Quiz $model
 * @method   Quiz getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuizRepository extends AbstractRepository implements QuizRepositoryInterface
{
    use HasFeatured;
    use HasSponsor;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    /**
     * @return string
     */
    public function model(): string
    {
        return Quiz::class;
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Quiz
     * @throws AuthorizationException | Exception
     */
    public function createQuiz(User $context, User $owner, array $attributes): Quiz
    {
        policy_authorize(QuizPolicy::class, 'create', $context);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'module_id'   => Quiz::ENTITY_TYPE,
            'is_approved' => (int) policy_check(QuizPolicy::class, 'autoApprove', $context, $owner),
        ]);

        $attributes['title'] = $this->cleanTitle($attributes['title']);

        if (!empty($attributes['temp_file'])) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;
        }

        $quiz = $this->getModel()->fill($attributes);
        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $quiz->setPrivacyListAttribute($attributes['list']);
        }

        $quiz->save();

        $this->createQuizQuestion($quiz, $attributes['questions']);

        if (!empty($attributes['attachments'])) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attributes['attachments'], $quiz);
        }

        return $this->with([
            'questions', 'questions.answers', 'quizText', 'results', 'results.items', 'attachments',
        ])->find($quiz->entityId());
    }

    /**
     * @param Quiz                 $quiz
     * @param array<string, mixed> $questions
     *
     * @return Quiz
     * @throws Exception
     */
    protected function createQuizQuestion(Quiz $quiz, array $questions): Quiz
    {
        foreach ($questions as $question) {
            $questionInstance = new Question([
                'question' => $this->cleanTitle($question['question']),
                'ordering' => $question['ordering'],
            ]);
            $questionInstance->quiz()->associate($quiz);
            $questionInstance->save();

            // Create new question answers
            $this->createQuizQuestionAnswer($questionInstance, $question['answers']);
        }

        return $quiz;
    }

    /**
     * @param Question             $question
     * @param array<string, mixed> $answers
     *
     * @return Question
     */
    protected function createQuizQuestionAnswer(Question $question, array $answers): Question
    {
        if (isset($answers['newAnswers'])) {
            $answers = $answers['newAnswers'];
        }

        $answerInstances = [];
        foreach ($answers as $answer) {
            $answerInstances[] = [
                'answer'     => $this->cleanTitle($answer['answer']),
                'is_correct' => $answer['is_correct'] ?? 0,
                'ordering'   => $answer['ordering'],
            ];
        }
        $question->answers()->createMany($answerInstances);

        return $question;
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Quiz
     * @throws AuthorizationException | Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateQuiz(User $context, int $id, array $attributes): Quiz
    {
        /** @var Quiz $quiz */
        $quiz = $this
            ->withUserMorphTypeActiveScope()
            ->getModel()
            ->newQuery()
            ->with(['questions', 'questions.answers', 'quizText', 'results', 'results.items', 'attachments'])
            ->find($id);
        policy_authorize(QuizPolicy::class, 'update', $context, $quiz);

        if (Arr::has($attributes, 'privacy') && !$context->can('updatePrivacy', [$quiz, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        if (isset($attributes['title'])) {
            $attributes['title'] = $this->cleanTitle($attributes['title']); //Todo: check allow url title
        }

        if (!empty($attributes['remove_photo'])) {
            if ($quiz->image_file_id) {
                app('storage')->rollDown($quiz->image_file_id);
                $attributes['image_file_id'] = 0;
            }
        }

        if (!empty($attributes['temp_file'])) {
            $storageFile                 = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $storageFile->id;

            // Delete temp file after done
            upload()->rollUp($storageFile->id);
        }

        $quiz->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $quiz->setPrivacyListAttribute($attributes['list']);
        }

        $quiz->save();

        $isResubmit = false;
        // Update/delete old questions if any
        if (isset($attributes['questions']['changedQuestions'])) {
            $this->updateQuizQuestions($quiz, $attributes['questions']['changedQuestions'], $isResubmit);
        }

        // Create new question if any
        if (isset($attributes['questions']['newQuestions'])) {
            $isResubmit = true;
            $this->createQuizQuestion($quiz, $attributes['questions']['newQuestions']);
        }

        if (isset($attributes['attachments'])) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attributes['attachments'], $quiz);
        }

        if ($isResubmit) {
            $results        = $quiz->results()->get()->all();
            $resultIds      = $quiz->results()->pluck('id')->toArray();
            $resultItems    = ResultDetail::query()->whereIn('result_id', $resultIds)->get();
            $submittedUsers = $quiz->results()->get()->pluck('user')->all();
            Notification::send($submittedUsers, new QuizResubmitNotifications($quiz));
            foreach ($resultItems as $resultItem) {
                $resultItem->delete();
            }
            foreach ($results as $result) {
                $result->delete();
            }
            $questionIds = $quiz->questions()->pluck('id')->toArray();
            Answer::query()->whereIn('question_id', $questionIds)->update(['total_play' => 0]);
        } else {
            UpdateQuizResultJob::dispatch($quiz);
        }

        $this->updateFeedStatus($quiz);

        return $quiz->refresh();
    }

    protected function updateFeedStatus(Quiz $quiz): void
    {
        app('events')->dispatch('activity.feed.mark_as_pending', [$quiz]);
    }

    /**
     * @param Quiz              $quiz
     * @param array<int, mixed> $questions
     *
     * @return Quiz
     * @throws Exception
     */
    protected function updateQuizQuestions(Quiz $quiz, array $questions, &$isResubmit): Quiz
    {
        $cachedQuestionIds = [];

        foreach ($quiz->questions as $currentQuestion) {
            /** @var Question $currentQuestion */
            if (!isset($questions[$currentQuestion->entityId()])) {
                ResultDetail::query()->where('question_id', '=', $currentQuestion->entityId())->delete();
                continue;
            }

            // All the current question ids not in this array shall be deleted
            $cachedQuestionIds[] = $currentQuestion->entityId();

            //Update question info
            $questionData = $questions[$currentQuestion->entityId()];
            if ($this->cleanTitle($questionData['question']) != $currentQuestion->question) {
                $isResubmit = true;
            }
            $currentQuestion->fill([
                'question' => $this->cleanTitle($questionData['question']),
                'ordering' => $questionData['ordering'],
            ]);
            $currentQuestion->save();

            $hasChangedAnswers = isset($questionData['answers']['changedAnswers']);
            $hasNewAnswers     = isset($questionData['answers']['newAnswers']);

            // Remove all old answer when only new answers input
            if ($hasNewAnswers && !$hasChangedAnswers) {
                $currentQuestion->answers->each(function (Answer $item) {
                    $item->delete();
                });
            }

            if ($hasChangedAnswers) {
                $this->updateQuizQuestionAnswer(
                    $currentQuestion,
                    $questionData['answers']['changedAnswers'],
                    $isResubmit
                );
            }

            if ($hasNewAnswers) {
                $isResubmit = true;
                $this->createQuizQuestionAnswer($currentQuestion, $questionData['answers']['newAnswers']);
            }
        }

        // Delete question ids which are not included
        $quiz->questions->except($cachedQuestionIds)->each(function (Question $item) {
            $item->delete();
        });

        return $quiz;
    }

    /**
     * @param Question             $question
     * @param array<string, mixed> $answers
     *
     * @return Question
     */
    protected function updateQuizQuestionAnswer(Question $question, array $answers, &$isResubmit): Question
    {
        $cachedAnswerIds = [];
        foreach ($question->answers as $oldAnswer) {
            if (isset($answers[$oldAnswer->entityId()])) {
                $cachedAnswerIds[] = $oldAnswer->entityId();

                $answerData = $answers[$oldAnswer->entityId()];

                ResultDetail::query()
                    ->where('answer_id', '=', $answerData['id'])
                    ->update(['is_correct' => $answerData['is_correct']]);
                if ($this->cleanTitle($answerData['answer']) != $oldAnswer->answer) {
                    $isResubmit = true;
                }

                $oldAnswer->fill([
                    'answer'     => $this->cleanTitle($answerData['answer']),
                    'is_correct' => $answerData['is_correct'] ?? 0,
                    'ordering'   => $answerData['ordering'],
                ]);
                $oldAnswer->save();
            } else {
                $isResubmit = true;
            }
        }

        // Delete answer ids which are not included
        $question->answers->except($cachedAnswerIds)->each(function (Answer $item) {
            $item->delete();
        });

        return $question;
    }

    public function calculateQuizResults(Quiz $quiz): void
    {
        $questionIds        = $quiz->questions->pluck('id')->toArray();
        $correctResultItems = ResultDetail::query()
            ->selectRaw('sum(is_correct) as total_correct, result_id')
            ->whereIn('question_id', $questionIds)
            ->groupBy('result_id')
            ->get()
            ->toArray();

        foreach ($correctResultItems as $correctResultItem) {
            Result::query()
                ->where('id', '=', $correctResultItem['result_id'])
                ->update(['total_correct' => $correctResultItem['total_correct']]);
        }
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewQuizzes(User $context, User $owner, array $attributes): Paginator
    {
        $view      = $attributes['view'];
        $limit     = $attributes['limit'];
        $profileId = $attributes['user_id'];

        $this->withUserMorphTypeActiveScope();

        if (Browse::VIEW_FEATURE == $view) {
            return $this->findFeature($limit);
        }

        if ($context->entityId() && $profileId == $context->entityId() && $view != Browse::VIEW_PENDING) {
            $attributes['view'] = $view = Browse::VIEW_MY;
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0) {
                if ($context->isGuest() || !$context->hasPermissionTo('quiz.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }

        $query     = $this->buildViewQuizzesQuery($context, $owner, $attributes);
        $relations = ['quizText', 'attachments'];

        return $query
            ->with($relations)
            ->simplePaginate($limit, ['quizzes.*']);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildViewQuizzesQuery(User $context, User $owner, array $attributes): Builder
    {
        $sort       = $attributes['sort'];
        $sortType   = $attributes['sort_type'];
        $when       = $attributes['when'];
        $search     = $attributes['q'];
        $searchTag  = $attributes['tag'] ?? '';
        $view       = $attributes['view'];
        $profileId  = $attributes['user_id'];
        $isFeatured = Arr::get($attributes, 'is_featured');

        $privacyScope = new PrivacyScope();
        $privacyScope
            ->setUserId($context->entityId())
            ->setModerationPermissionName('quiz.moderate')
            ->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($searchTag != '') {
            $query = $query->addScope(new TagScope($searchTag));
        }

        $query->addScope(new FeaturedScope($isFeatured));

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());

            $viewScope->setIsViewOwner(true);

            if (!$context->can('approve', [Quiz::class, resolve(Quiz::class)])) {
                $query->where('quizzes.is_approved', '=', Quiz::IS_APPROVED);
            }
        }

        $query = $this->applyDisplaySetting($query, $owner, $view);

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    /**
     * @param  Builder $query
     * @param  User    $owner
     * @param  string  $view
     * @return Builder
     */
    private function applyDisplaySetting(Builder $query, User $owner, string $view): Builder
    {
        if ($view == Browse::VIEW_MY) {
            return $query;
        }

        if (!$owner instanceof HasPrivacyMember) {
            $query->where('quizzes.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator
    {
        return $this->withUserMorphTypeActiveScope()
            ->getModel()
            ->newQuery()
            ->where('is_featured', Quiz::IS_FEATURED)
            ->where('is_approved', Quiz::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    /**
     * @param int $limit
     *
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->withUserMorphTypeActiveScope()
            ->getModel()
            ->newQuery()
            ->where('is_sponsor', Quiz::IS_SPONSOR)
            ->where('is_approved', Quiz::IS_APPROVED)
            ->simplePaginate($limit);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Quiz
     * @throws AuthorizationException
     * @throws Exception
     */
    public function viewQuiz(User $context, int $id): Quiz
    {
        /** @var Quiz $quiz */
        $quiz = $this->withUserMorphTypeActiveScope()->with([
            'questions', 'questions.answers', 'quizText', 'results', 'results.items', 'attachments',
        ])->find($id);

        policy_authorize(QuizPolicy::class, 'view', $context, $quiz);

        $quiz->incrementTotalView();

        return $quiz;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteQuiz(User $context, int $id): bool
    {
        $quiz = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(QuizPolicy::class, 'delete', $context, $quiz);

        return (bool) $this->delete($id);
    }
}
