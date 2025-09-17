<?php

namespace MetaFox\Ban\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Ban\Facades\Ban;
use MetaFox\Ban\Http\Requests\v1\BanRule\Admin\BatchDeleteRequest;
use MetaFox\Ban\Http\Requests\v1\BanRule\Admin\CreateFormRequest;
use MetaFox\Ban\Http\Requests\v1\BanRule\Admin\IndexRequest;
use MetaFox\Ban\Http\Requests\v1\BanRule\Admin\StoreRequest;
use MetaFox\Ban\Http\Resources\v1\BanRule\Admin\BanRuleItem as Item;
use MetaFox\Ban\Http\Resources\v1\BanRule\Admin\BanRuleItemCollection as ItemCollection;
use MetaFox\Ban\Http\Resources\v1\BanRule\Admin\StoreBanRuleForm;
use MetaFox\Ban\Repositories\BanRuleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Ban\Http\Controllers\Api\BanRuleAdminController::$controllers;.
 */

/**
 * Class BanRuleAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BanRuleAdminController extends ApiController
{
    /**
     * @var BanRuleRepositoryInterface
     */
    private BanRuleRepositoryInterface $repository;

    /**
     * BanRuleAdminController Constructor.
     *
     * @param BanRuleRepositoryInterface $repository
     */
    public function __construct(BanRuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewBanRule(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $type = Arr::get($params, 'type');

        $data = $this->repository->createBanRule(user(), $params);

        Artisan::call('cache:reset');

        return $this->success(
            new Item($data),
            $this->geMetaData($type),
            __p('ban::phrase.ban_rule_was_created_successfully')
        );
    }

    protected function geMetaData(string $type): array
    {
        $actionMeta = new ActionMeta();

        return $actionMeta->nextAction()
            ->type('navigate')
            ->payload(
                PayloadActionMeta::payload()->url("/ban/$type/browse")
            )->toArray();
    }

    /**
     * @param CreateFormRequest $request
     * @return AbstractForm
     */
    public function getCreateForm(CreateFormRequest $request): AbstractForm
    {
        $params = $request->validated();
        $type   = Arr::get($params, 'resourceName');

        $form = new StoreBanRuleForm();
        $form->setHandler(Ban::resolveTypeHandler($type));

        return $form;
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteBanRule($id);

        Artisan::call('cache:reset');

        return $this->success([
            'id' => $id,
        ], [], __p('ban::phrase.ban_rule_was_deleted_successfully'));
    }

    /**
     * @param BatchDeleteRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchDelete(BatchDeleteRequest $request): JsonResponse
    {
        $params = $request->validated();

        $ruleIds = Arr::get($params, 'id', []);

        foreach ($ruleIds as $id) {
            $this->repository->deleteBanRule($id);
        }

        Artisan::call('cache:reset');

        return $this->success([], [], __p('ban::phrase.ban_rule_s_was_deleted_successfully'));
    }

    /**
     * Active menu item.
     *
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @group admin/menu
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $this->repository->update([
            'is_active' => $params['active'],
        ], $id);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('core::phrase.already_saved_changes'));
    }
}
