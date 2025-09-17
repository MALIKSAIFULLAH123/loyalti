<?php

namespace MetaFox\Announcement\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Announcement\Http\Requests\v1\Announcement\Admin\IndexRequest;
use MetaFox\Announcement\Http\Requests\v1\Announcement\Admin\StoreRequest;
use MetaFox\Announcement\Http\Requests\v1\Announcement\Admin\UpdateRequest;
use MetaFox\Announcement\Http\Resources\v1\Announcement\Admin\AnnouncementDetail as Detail;
use MetaFox\Announcement\Http\Resources\v1\Announcement\Admin\AnnouncementItem as Item;
use MetaFox\Announcement\Http\Resources\v1\Announcement\Admin\AnnouncementItemCollection as ItemCollection;
use MetaFox\Announcement\Http\Resources\v1\Announcement\Admin\StoreAnnouncementForm;
use MetaFox\Announcement\Http\Resources\v1\Announcement\Admin\UpdateAnnouncementForm;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Announcement\Http\Controllers\Api\AnnouncementAdminController::$controllers;.
 */

/**
 * Class AnnouncementAdminController.
 *
 * @codeCoverageIgnore
 * @ignore
 * @group announcement
 * @admincp
 * @authenticated
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AnnouncementAdminController extends ApiController
{
    /**
     * @var AnnouncementRepositoryInterface
     */
    private AnnouncementRepositoryInterface $repository;

    /**
     * AnnouncementAdminController Constructor.
     *
     * @param AnnouncementRepositoryInterface $repository
     */
    public function __construct(AnnouncementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<Item>
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();
        $data    = $this->repository->viewAnnouncementsForAdmin($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Create announcement.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->createAnnouncement(user(), $params);

        $this->navigate($data->admin_browse_url);

        Artisan::call('cache:reset');

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url' => '/announcement/announcement/browse',
                ],
            ],
        ], __p('announcement::phrase.announcement_created_successfully'));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewAnnouncement(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update announcement.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateAnnouncement(user(), $id, $params);

        $this->navigate($data->admin_browse_url);

        Artisan::call('cache:reset');
        return $this->success(new Detail($data), [], __p('announcement::phrase.announcement_updated_successfully'));
    }

    /**
     * Delete announcement.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteAnnouncement(user(), $id);

        Artisan::call('cache:reset');
        return $this->success([
            'id' => $id,
        ], [], __p('announcement::phrase.announcement_deleted_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $isActive = Arr::get($params, 'active', 1);

        $package = match ($isActive) {
            1       => $this->repository->activateAnnouncement($context, $id),
            0       => $this->repository->deactivateAnnouncement($context, $id),
            default => null,
        };

        if (null === $package) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        Artisan::call('cache:reset');
        return $this->success(new Detail($package), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * View creation form.
     *
     * @return StoreAnnouncementForm
     */
    public function create(): StoreAnnouncementForm
    {
        return new StoreAnnouncementForm();
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->with(['roles'])->find($id);
        $form = new UpdateAnnouncementForm($item);

        return $this->success($form);
    }
}
