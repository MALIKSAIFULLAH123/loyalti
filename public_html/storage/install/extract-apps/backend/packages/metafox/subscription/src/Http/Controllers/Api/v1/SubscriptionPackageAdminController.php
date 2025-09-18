<?php

namespace MetaFox\Subscription\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\ActiveRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\IndexRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\PopularRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\StoreRequest;
use MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin\UpdateRequest;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\CreateSubscriptionPackageForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\EditSubscriptionPackageForm;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\SubscriptionPackageDetail as Detail;
use MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin\SubscriptionPackageItemCollection as ItemCollection;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as PackageFacade;
use MetaFox\Subscription\Support\Helper;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Subscription\Http\Controllers\Api\SubscriptionPackageAdminController::$controllers.
 */

/**
 * Class SubscriptionPackageAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SubscriptionPackageAdminController extends ApiController
{
    /**
     * @var SubscriptionPackageRepositoryInterface
     */
    private SubscriptionPackageRepositoryInterface $repository;

    /**
     * SubscriptionPackageAdminController Constructor.
     *
     * @param SubscriptionPackageRepositoryInterface $repository
     */
    public function __construct(SubscriptionPackageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->viewPackages($context, $params);

        $meta = [];

        if (!$data->count()) {
            $meta = [
                'empty_message' => __p('subscription::admin.no_packages_have_been_added'),
            ];

            if (Arr::get($params, 'view') === Browse::VIEW_SEARCH) {
                Arr::set($meta, 'empty_message', __p('subscription::admin.no_packages_found'));
            }
        }

        return $this->success(new ItemCollection($data), $meta);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->createPackage($context, $params);

        $nextAction = [
            'type'    => 'navigate',
            'payload' => [
                'url'     => '/subscription/package/browse',
                'replace' => true,
            ],
        ];

        return $this->success(
            new Detail($data),
            ['nextAction' => $nextAction],
            __p('subscription::admin.package_successfully_created')
        );
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $context = user();

        $data = $this->repository->viewPackage($context, $id, [
            'view' => Helper::VIEW_ADMINCP,
        ]);

        return new Detail($data);
    }

    /**
     * @param  UpdateRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->updatePackage($context, $id, $params);

        if ($data->is_popular) {
            $data->title = PackageFacade::resolvePopularTitle($data->title);
        }

        return $this->success(new Detail($data), [], __p('subscription::admin.package_successfully_updated'));
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
        $context = user();

        $this->repository->deletePackage($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('subscription::admin.package_successfully_deleted'));
    }

    /**
     * Get creation form.
     *
     * @return CreateSubscriptionPackageForm
     */
    public function create(): CreateSubscriptionPackageForm
    {
        return new CreateSubscriptionPackageForm();
    }

    /**
     * Get updating form.
     *
     * @param int $id
     *
     * @return EditSubscriptionPackageForm
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(int $id): EditSubscriptionPackageForm
    {
        $resource = $this->repository->find($id);

        return new EditSubscriptionPackageForm($resource);
    }

    /**
     * @param  PopularRequest          $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function markAsPopular(PopularRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        $isPolular = Arr::get($data, 'is_popular', false);

        $context = user();

        $this->repository->markAsPopular($context, $id, $isPolular);

        return $this->success([
            'id'         => $id,
            'is_popular' => $isPolular,
        ]);
    }

    /**
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();

        $data = $request->validated();

        $isActive = Arr::get($data, 'active', false);

        $this->repository->activePackage($context, $id, $isActive);

        return $this->success([
            'id'        => $id,
            'is_active' => (int) $data['active'],
        ], [], __p('core::phrase.already_saved_changes'));
    }
}
