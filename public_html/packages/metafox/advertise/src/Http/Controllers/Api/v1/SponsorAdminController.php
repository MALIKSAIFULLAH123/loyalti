<?php

namespace MetaFox\Advertise\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin\EditSponsorForm;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin\SponsorItemCollection as ItemCollection;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin\SponsorDetail as Detail;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\Admin\IndexRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\Admin\StoreRequest;
use MetaFox\Advertise\Http\Requests\v1\Sponsor\Admin\UpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Advertise\Http\Controllers\Api\SponsorAdminController::$controllers;
 */

/**
 * Class SponsorAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class SponsorAdminController extends ApiController
{
    /**
     * @var SponsorRepositoryInterface
     */
    private SponsorRepositoryInterface $repository;

    /**
     * SponsorAdminController Constructor.
     *
     * @param SponsorRepositoryInterface $repository
     */
    public function __construct(SponsorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        policy_authorize(SponsorPolicy::class, 'viewAdminCP', user());

        $data = $this->repository->viewAdminCP($params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->create($params);

        return new Detail($data);
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
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $this->repository->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'update', $context, $sponsor);

        if ($sponsor->is_ended) {
            unset($params['start_date']);
            unset($params['end_date']);
        }

        $data = $this->repository->updateSponsor($context, $sponsor, $params);

        return $this->success(new Detail($data), [], __p('advertise::phrase.sponsor_successfully_updated'));
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
        $sponsor = $this->repository->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'delete', $context, $sponsor);

        $this->repository->deleteSponsor($sponsor);

        return $this->success([
            'id' => $id,
        ], [], __p('advertise::phrase.sponsor_successfully_deleted'));
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $active = $request->get('active');

        if (!is_int($active)) {
            $active = 1;
        }

        $active = (bool) $active;

        $context = user();

        policy_authorize(SponsorPolicy::class, 'update', $context);

        $sponsor = $this->repository->find($id);

        $this->repository->activeSponsor($sponsor, $active);

        $message = match ($active) {
            true  => __p('advertise::phrase.sponsor_successfully_activated'),
            false => __p('advertise::phrase.sponsor_successfully_deactivated'),
        };

        return $this->success([], [], $message);
    }

    /**
     * @param  int          $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $advertise = $this->repository->find($id);

        $form = new EditSponsorForm($advertise);

        return $this->success($form);
    }

    public function approve(int $id): JsonResponse
    {
        $context = user();

        $sponsor = $this->repository->find($id);

        policy_authorize(SponsorPolicy::class, 'approve', $context, $sponsor);

        $this->repository->approveSponsor($context, $sponsor);

        return $this->success([], [], __p('advertise::phrase.sponsor_successfully_approved'));
    }

    public function deny(int $id): JsonResponse
    {
        $context = user();

        $sponsor = $this->repository->find($id);

        policy_authorize(SponsorPolicy::class, 'deny', $context, $sponsor);

        $this->repository->denySponsor($context, $sponsor);

        return $this->success([], [], __p('advertise::phrase.sponsor_successfully_denied'));
    }

    public function markAsPaid(int $id): JsonResponse
    {
        $context = user();

        $sponsor = $this->repository->find($id);

        policy_authorize(SponsorPolicy::class, 'markAsPaid', $context, $sponsor);

        $this->repository->markAsPaid($context, $sponsor);

        return $this->success([], [], __p('advertise::phrase.sponsor_successfully_marked_as_paid'));
    }
}
