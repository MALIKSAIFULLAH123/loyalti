<?php

namespace MetaFox\Announcement\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Announcement\Http\Requests\v1\AnnouncementView\IndexRequest;
use MetaFox\Announcement\Http\Requests\v1\AnnouncementView\StoreRequest;
use MetaFox\Announcement\Http\Resources\v1\AnnouncementView\AnnouncementViewDetail as Detail;
use MetaFox\Announcement\Http\Resources\v1\AnnouncementView\AnnouncementViewItemCollection as ItemCollection;
use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

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
class AnnouncementViewController extends ApiController
{
    /**
     * @var AnnouncementViewRepositoryInterface
     */
    private AnnouncementViewRepositoryInterface $repository;

    /**
     * @param AnnouncementViewRepositoryInterface $repository
     */
    public function __construct(AnnouncementViewRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse announcement.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @group announcement
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $data = $this->repository->viewAnnouncementViews($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Hide announcement.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group announcement
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $result = $this->repository->createAnnouncementView($context, $params);

        return $this->success(new Detail($result), [], __p('announcement::phrase.marked_as_read_successfully'));
    }
}
