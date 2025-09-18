<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\LiveVideoDetail;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Repositories\NotificationSettingRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\LiveStreaming\Http\Controllers\Api\NotificationController::$controllers;.
 */

/**
 * Class NotificationController.
 * @codeCoverageIgnore
 * @ignore
 */
class NotificationController extends ApiController
{
    /**
     * @var NotificationSettingRepositoryInterface
     */
    private NotificationSettingRepositoryInterface $repository;
    private LiveVideoRepositoryInterface $liveVideoRepository;

    /**
     * NotificationController Constructor.
     *
     * @param NotificationSettingRepositoryInterface $repository
     * @param LiveVideoRepositoryInterface           $liveVideoRepository
     */
    public function __construct(NotificationSettingRepositoryInterface $repository, LiveVideoRepositoryInterface $liveVideoRepository)
    {
        $this->repository          = $repository;
        $this->liveVideoRepository = $liveVideoRepository;
    }

    /**
     * @throws AuthenticationException
     * @throws Exception
     */
    public function offNotification($id): JsonResponse
    {
        $liveVideo  = $this->liveVideoRepository->find($id);
        $user       = user();
        $owner      = $liveVideo->user;

        $model = $this->repository->disabledNotification($owner, $user);

        if ($model) {
            return $this->success(new LiveVideoDetail($liveVideo), [], __p('livestreaming::phrase.turned_off_notification_successfully', ['user' => $liveVideo->user->toTitle()]));
        }

        return $this->error(__p('validation.something_went_wrong_please_try_again'));
    }

    /**
     * @throws AuthenticationException
     * @throws Exception
     */
    public function onNotification($id): JsonResponse
    {
        $liveVideo  = $this->liveVideoRepository->find($id);
        $user       = user();
        $owner      = $liveVideo->user;

        if ($this->repository->enabledNotification($owner, $user)) {
            return $this->success(new LiveVideoDetail($liveVideo), [], __p('livestreaming::phrase.turned_on_notification_successfully', ['user' => $liveVideo->user->toTitle()]));
        }

        return $this->error(__p('validation.something_went_wrong_please_try_again'));
    }
}
