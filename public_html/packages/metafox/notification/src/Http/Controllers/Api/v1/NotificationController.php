<?php

namespace MetaFox\Notification\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Notification\Http\Requests\v1\Notification\IndexRequest;
use MetaFox\Notification\Http\Resources\v1\Notification\NotificationItemCollection as ItemCollection;
use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Notification\Http\Controllers\Api\NotificationController::$controllers;.
 */

/**
 * Class NotificationController.
 * @ignore
 * @codeCoverageIgnore
 * @group notification
 * @authenticated
 */
class NotificationController extends ApiController
{
    /**
     * @var NotificationRepositoryInterface
     */
    private NotificationRepositoryInterface $repository;

    /**
     * @param NotificationRepositoryInterface $repository
     */
    public function __construct(NotificationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse current user's notifications.
     *
     * @param  IndexRequest            $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @bodyParam page int The page number. Example: 1
     * @bodyParam limit int The max item per page. Example: 4
     * @usesPagination
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->getNotifications(user(), $params);

        return $this->success(new ItemCollection($data), [], '');
    }

    /**
     * Mark as read.
     *
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function markAsRead(int $id): JsonResponse
    {
        $return = $this->repository->markAsRead(user(), $id);
        if (!$return) {
            return $this->error(__p('notification::phrase.cannot_mark_notification_as_read'));
        }

        return $this->success(['id' => $id], [], __p('notification::phrase.marked_as_read_successfully'));
    }

    /**
     * Mark as unread.
     *
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function markAsUnread(int $id): JsonResponse
    {
        $return = $this->repository->markAsUnread(user(), $id);
        if (!$return) {
            return $this->error(__p('notification::phrase.cannot_mark_notification_as_unread'));
        }

        return $this->success(['id' => $id], [], __p('notification::phrase.marked_as_unread_successfully'));
    }

    /**
     * Mark all as read.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function markAllAsRead(): JsonResponse
    {
        $return = $this->repository->markAllAsRead(user());
        if (!$return) {
            return $this->error(__p('notification::phrase.cannot_marked_notification_as_read'));
        }

        return $this->success([], [], __p('notification::phrase.marked_all_as_read_successfully'));
    }

    /**
     * Delete notification.
     *
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteNotification(user(), $id);

        return $this->success(['id' => $id], [], __p('notification::phrase.notification_removed'));
    }

    /**
     * @throws AuthenticationException
     */
    public function destroyAll(): JsonResponse
    {
        $context = user();
        $this->repository->deleteNotificationsByNotifiable($context);

        return $this->success([], [], __p('notification::phrase.all_notification_removed'));
    }
}
