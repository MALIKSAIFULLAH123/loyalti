<?php

namespace MetaFox\Announcement\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Announcement\Http\Requests\v1\Announcement\CloseRequest;
use MetaFox\Announcement\Http\Requests\v1\Announcement\HideRequest;
use MetaFox\Announcement\Http\Requests\v1\Announcement\IndexRequest;
use MetaFox\Announcement\Http\Resources\v1\Announcement\AnnouncementDetail as Detail;
use MetaFox\Announcement\Http\Resources\v1\Announcement\AnnouncementItemCollection as ItemCollection;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Repositories\AnnouncementCloseRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Announcement\Http\Controllers\Api\AnnouncementController::$controllers;
 */

/**
 * Class AnnouncementController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group announcement
 */
class AnnouncementController extends ApiController
{
    /**
     * @var AnnouncementRepositoryInterface
     */
    private AnnouncementRepositoryInterface $repository;
    /**
     * @var AnnouncementCloseRepositoryInterface
     */
    private AnnouncementCloseRepositoryInterface $closeRepository;

    /**
     * @param AnnouncementRepositoryInterface      $repository
     * @param AnnouncementCloseRepositoryInterface $closeRepository
     */
    public function __construct(
        AnnouncementRepositoryInterface $repository,
        AnnouncementCloseRepositoryInterface $closeRepository
    ) {
        $this->repository      = $repository;
        $this->closeRepository = $closeRepository;
    }

    /**
     * Browse announcement.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @group announcement
     * @throws AuthorizationException|AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        /**
         * @var Collection $items
         */

        [$total, $items] = $this->repository->viewAnnouncementsWithLastId($context, $params);

        $lastId = null;

        if ($items->count()) {
            $lastItem = $items->last();

            if ($lastItem instanceof Announcement) {
                $lastId = $lastItem->entityId();
            }
        }

        $meta = [
            'total'        => $total,
            'total_unread' => $this->repository->getTotalUnread($context),
        ];

        $pagination = [
            'last_id' => $lastId,
        ];

        return response()->json([
            'data' => (new ItemCollection($items))->toArray($request),
            'meta' => $meta,
            'pagination' => $pagination,
        ]);
    }

    /**
     * View announcement.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewAnnouncement(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Hide announcement.
     *
     * @param HideRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function hide(HideRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->hideAnnouncement(user(), $params['announcement_id']);

        return $this->success([
            'id' => $params['announcement_id'],
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function close(CloseRequest $request): JsonResponse
    {
        $params = $request->validated();

        $this->repository->closeAnnouncement(user(), $params['announcement_id']);

        return $this->success();
    }
}
