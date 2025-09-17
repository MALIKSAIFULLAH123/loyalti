<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Localize\Http\Requests\v1\CountryCity\Admin\IndexRequest;
use MetaFox\Localize\Http\Requests\v1\CountryCity\Admin\StoreRequest;
use MetaFox\Localize\Http\Requests\v1\CountryCity\Admin\UpdateRequest;
use MetaFox\Localize\Http\Resources\v1\CountryCity\Admin\CountryCityDetail as Detail;
use MetaFox\Localize\Http\Resources\v1\CountryCity\Admin\CountryCityItemCollection as ItemCollection;
use MetaFox\Localize\Http\Resources\v1\CountryCity\Admin\StoreCountryCityForm;
use MetaFox\Localize\Http\Resources\v1\CountryCity\Admin\UpdateCountryCityForm;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Repositories\CountryCityRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\CountryCityAdminController::$controllers.
 */

/**
 * Class CountryCityAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class CountryCityAdminController extends ApiController
{
    /**
     * @var CountryCityRepositoryInterface
     */
    private CountryCityRepositoryInterface $repository;

    /**
     * CountryCityAdminController Constructor.
     *
     * @param CountryCityRepositoryInterface $repository
     */
    public function __construct(CountryCityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $query = $this->repository->getModel()->newQuery();

        $search  = Arr::get($params, 'q');
        $stateId = Arr::get($params, 'state_id');

        if ($stateId) {
            $searchScope = new SearchScope($search, ['name']);
            $query       = $query->addScope($searchScope);
        }

        if ($stateId) {
            $state = CountryChild::find($stateId);
            $query = $query->where('state_code', '=', $state->state_code);
        }

        $data = $query->orderBy('name')->paginate($params['limit'] ?? 100);

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
        $data   = $this->repository->createCity(user(), $params);

        $this->navigate($data->admin_browse_url, true);

        return $this->success(new Detail($data), [], __p('localize::admin.country_city_created_successfully'));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateCity(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('localize::admin.country_city_updated_successfully'));
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
        $this->repository->deleteCity(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('localize::admin.country_city_deleted_successfully'));
    }

    /**
     * Get creation form.
     *
     * @return StoreCountryCityForm
     */
    public function create(Request $request): StoreCountryCityForm
    {
        $form = new StoreCountryCityForm();

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $form;
    }

    /**
     * Get updating form.
     *
     * @param int $id
     *
     * @return UpdateCountryCityForm
     */
    public function edit(int $id): UpdateCountryCityForm
    {
        $resource = $this->repository->find($id);

        return new UpdateCountryCityForm($resource);
    }
}
