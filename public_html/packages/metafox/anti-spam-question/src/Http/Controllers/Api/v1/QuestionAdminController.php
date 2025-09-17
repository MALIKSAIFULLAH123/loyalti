<?php

namespace MetaFox\AntiSpamQuestion\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin\IndexRequest;
use MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin\StoreRequest;
use MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin\UpdateRequest;
use MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin\QuestionDetail as Detail;
use MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin\QuestionItemCollection as ItemCollection;
use MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin\StoreQuestionForm;
use MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin\UpdateQuestionForm;
use MetaFox\AntiSpamQuestion\Repositories\QuestionAdminRepositoryInterface;
use MetaFox\AntiSpamQuestion\Support\Constants;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\AntiSpamQuestion\Http\Controllers\Api\QuestionAdminController::$controllers;
 */

/**
 * Class QuestionAdminController
 * @codeCoverageIgnore
 * @ignore
 */
class QuestionAdminController extends ApiController
{
    /**
     * QuestionAdminController Constructor
     *
     * @param QuestionAdminRepositoryInterface $repository
     */
    public function __construct(protected QuestionAdminRepositoryInterface $repository) {}

    /**
     * Browse item
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $limit  = Arr::get($params, 'limit', 100);
        $data   = $this->repository->viewQuestions(user(), $params)->paginate($limit, ['asq_questions.*']);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->createQuestion($params);

        $this->navigate('antispamquestion/question/browse');
        Artisan::call('cache:reset');

        return $this->success([], [], __p('antispamquestion::phrase.question_was_created_successfully'));
    }

    /**
     * Update item
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $question = $this->repository->find($id);
        $this->repository->updateQuestion($question, $params);

        $this->navigate('antispamquestion/question/browse');
        Artisan::call('cache:reset');

        return $this->success([], [], __p('antispamquestion::phrase.question_was_updated_successfully'));
    }

    /**
     * Delete item
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $model->delete();

        return $this->success([], [], __p('antispamquestion::phrase.question_was_deleted_successfully'));
    }

    public function create(): StoreQuestionForm
    {
        return new StoreQuestionForm();
    }

    public function edit(int $id): UpdateQuestionForm
    {
        $model = $this->repository->find($id);

        return new UpdateQuestionForm($model);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids', []);

        $this->repository->ordering(user(), $orderIds);

        Cache::forget(Constants::CACHE_KEY_ASQ_ALL_ACTIVE_QUESTIONS);
        return $this->success([], [], __p('antispamquestion::phrase.question_reordered_successfully'));
    }

    /**
     * Toggle active status of a question
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $model->update(['is_active' => !$model->is_active]);
        Cache::forget(Constants::CACHE_KEY_ASQ_ALL_ACTIVE_QUESTIONS);

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Toggle active status of a question
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleCaseSensitive(int $id): JsonResponse
    {
        $model = $this->repository->find($id);

        $model->update(['is_case_sensitive' => !$model->is_case_sensitive]);

        Cache::forget(Constants::CACHE_KEY_ASQ_ALL_ACTIVE_QUESTIONS);

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }
}
