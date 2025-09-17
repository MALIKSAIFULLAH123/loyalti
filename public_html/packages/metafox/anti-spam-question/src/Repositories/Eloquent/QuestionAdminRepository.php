<?php

namespace MetaFox\AntiSpamQuestion\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\AntiSpamQuestion\Repositories\AnswerAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Support\Constants;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class QuestionAdminRepository
 * @method Question find($id, $columns = ['*'])
 */
class QuestionAdminRepository extends AbstractRepository implements QuestionAdminRepositoryInterface
{
    public function model()
    {
        return Question::class;
    }

    protected AnswerAdminRepositoryInterface $answerAdminRepository;

    public function boot()
    {
        $this->answerAdminRepository = app(AnswerAdminRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Builder
     */
    public function viewQuestions(User $context, array $attributes): Builder
    {
        $query = $this->getModel()->newQuery();
        $table = $this->getModel()->getTable();

        $search          = Arr::get($attributes, 'q');
        $isActive        = Arr::get($attributes, 'is_active');
        $isCaseSensitive = Arr::get($attributes, 'is_case_sensitive');
        $createdFrom     = Arr::get($attributes, 'created_from');
        $createdTo       = Arr::get($attributes, 'created_to');

        if (!empty($search)) {
            $defaultLocale = Language::getDefaultLocaleId();

            $searchScope = new SearchScope($search, ['ps.text']);
            $searchScope->asLeftJoin();
            $searchScope->setTableField('question');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');

            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        if (isset($isActive)) {
            $query->where("$table.is_active", (bool) $isActive);
        }

        if (isset($isCaseSensitive)) {
            $query->where("$table.is_case_sensitive", (bool) $isCaseSensitive);
        }

        if (isset($createdFrom)) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if (isset($createdTo)) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        return $query->orderBy("$table.ordering")->orderBy("$table.created_at", 'desc');
    }

    /**
     * @inheritDoc
     */
    public function ordering(User $context, array $orders): void
    {
        foreach ($orders as $order => $id) {
            $this->getModel()->newQuery()
                ->where('id', $id)->update(['ordering' => $order]);
        }
    }

    /**
     * @inheritDoc
     */
    public function createQuestion(array $data): Question
    {
        $answers = Arr::get($data, 'answers', []);

        $newAnswers = array_filter($answers, function ($answer) {
            return Arr::get($answer, 'status') === MetaFoxConstant::FILE_NEW_STATUS;
        });

        Arr::forget($data, ['answers']);

        if (Arr::has($data, 'file')) {
            $file = $this->handleFile($data['file']);
            $data = array_merge($data, $file);
            Arr::forget($data, 'file');
        }

        Arr::set($data, 'ordering', $this->getMaxOrdering() + 1);
        /** @var Question $model */
        $model = $this->getModel()->fill(attributes: $data);
        $model->save();

        if (!empty($newAnswers)) {
            $this->answerAdminRepository->createAnswer($model, $newAnswers);
        }

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function updateQuestion(Question $model, array $data): Question
    {
        $answers = Arr::get($data, 'answers', []);

        $newAnswers = array_filter($answers, function ($answer) {
            return Arr::get($answer, 'status') === MetaFoxConstant::FILE_NEW_STATUS;
        });

        $updateAnswers = array_filter($answers, function ($answer) {
            return Arr::get($answer, 'status') === MetaFoxConstant::FILE_UPDATE_STATUS;
        });

        $deleteAnswers = array_filter($answers, function ($answer) {
            return Arr::get($answer, 'status') === MetaFoxConstant::FILE_REMOVE_STATUS;
        });

        Arr::forget($data, ['answers']);

        if (Arr::has($data, 'file')) {
            $file = $this->handleFile($data['file']);
            $data = array_merge($data, $file);
            Arr::forget($data, 'file');
        }

        $model->update($data);

        if (!empty($newAnswers)) {
            $this->answerAdminRepository->createAnswer($model, $newAnswers);
        }

        if (!empty($updateAnswers)) {
            $this->answerAdminRepository->updateAnswer($model, $updateAnswers);
        }

        if (!empty($deleteAnswers)) {
            $this->answerAdminRepository->removeAnswers($model, $deleteAnswers);
        }

        return $model;
    }

    protected function handleFile(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        if (Arr::has($data, 'status') && $data['status'] === MetaFoxConstant::FILE_REMOVE_STATUS) {
            return ['image_file_id' => null];
        }

        if (Arr::has($data, 'temp_file')) {
            $tempFile = upload()->getFile($data['temp_file']);

            upload()->rollUp($data['temp_file']);

            return ['image_file_id' => $tempFile->id];
        }

        return [];
    }

    /**
     * Cache all active questions with their answers
     *
     * @return Collection
     */
    public function cacheActiveQuestions(): Collection
    {
        return Cache::rememberForever(Constants::CACHE_KEY_ASQ_ALL_ACTIVE_QUESTIONS, function () {
            return $this->getModel()->newQuery()
                ->where('is_active', true)
                ->orderBy('ordering')
                ->with('answers')
                ->get();
        });
    }

    protected function getMaxOrdering(): int
    {
        return $this->getModel()->newQuery()->max('ordering') ?? 0;
    }
}
