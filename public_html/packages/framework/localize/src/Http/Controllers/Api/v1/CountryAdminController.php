<?php

namespace MetaFox\Localize\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Form\AbstractForm;
use MetaFox\Localize\Http\Requests\v1\Country\Admin\BatchActiveRequest;
use MetaFox\Localize\Http\Requests\v1\Country\Admin\StoreRequest;
use MetaFox\Localize\Http\Requests\v1\Country\Admin\UpdateRequest;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\CountryDetail as Detail;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\CountryItemCollection as ItemCollection;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\StoreCountryForm;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\TranslateCountryForm;
use MetaFox\Localize\Http\Resources\v1\Country\Admin\UpdateCountryForm;
use MetaFox\Localize\Repositories\CountryRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\Http\Requests\v1\OrderingRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CountryController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group admin/country
 * @authenticated
 */
class CountryAdminController extends ApiController
{
    public CountryRepositoryInterface $repository;

    public function __construct(CountryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     */
    public function index(Request $request)
    {
        $params = $request->all(['q', 'limit']);

        $data = $this->repository->viewCountries(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthorizationException|AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->createCountry(user(), $params);

        return $this->success([], [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/localize/country/browse',
                    'replace' => true,
                ],
            ],
        ], __p('localize::admin.country_successfully_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthorizationException|AuthenticationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewCountry(user(), $id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateCountry(user(), $id, $params);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [], __p('localize::admin.country_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteCountry(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('localize::admin.country_deleted_successfully'));
    }

    /**
     * @param ActiveRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $active = $params['active'] ? 1 : 0;

        $country = $this->repository->updateCountry(user(), $id, ['is_active' => $active]);

        Artisan::call('cache:reset');

        return $this->success([new Detail($country)], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * @param OrderingRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function order(OrderingRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->orderCountries(user(), $params['orders']);

        return $this->success();
    }

    /**
     * @param BatchActiveRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function batchActive(BatchActiveRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->batchActiveCountries(user(), $params['id'], $params['active']);

        return $this->success();
    }

    /**
     * @param int $id
     *
     * @return AbstractForm
     */
    public function edit(int $id): AbstractForm
    {
        $resource = $this->repository->find($id);

        // if not found

        return new UpdateCountryForm($resource);
    }

    /**
     * @return AbstractForm
     */
    public function create(): AbstractForm
    {
        return new StoreCountryForm(null);
    }

    /**
     * @param int $id
     *
     * @return TranslateCountryForm
     */
    public function getTranslateForm(int $id): AbstractForm
    {
        $resource = $this->repository->find($id);

        return new TranslateCountryForm($resource);
    }
}
