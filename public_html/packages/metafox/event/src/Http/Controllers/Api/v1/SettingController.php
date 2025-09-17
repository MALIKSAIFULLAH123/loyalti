<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Event\Http\Requests\v1\Setting\UpdateRequest;
use MetaFox\Event\Http\Resources\v1\Event\SettingForm;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class SettingController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SettingController extends ApiController
{
    public EventRepositoryInterface $eventRepository;
    public UserPrivacyRepositoryInterface $privacyRepository;

    public function __construct(EventRepositoryInterface $eventRepository, UserPrivacyRepositoryInterface $privacyRepository)
    {
        $this->eventRepository   = $eventRepository;
        $this->privacyRepository = $privacyRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function form(int $id)
    {
        $context = user();
        $event   = $this->eventRepository->find($id);

        policy_authorize(EventPolicy::class, 'update', $context, $event);

        $settings = $this->privacyRepository->getProfileSettings($id);

        return $this->success(new SettingForm($settings, $id, $event));
    }

    /**
     * @throws AuthenticationException|AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $context = user();
        $event   = $this->eventRepository->find($id);

        policy_authorize(EventPolicy::class, 'update', $context, $event);

        $updateParams  = $request->validated();
        $settingParams = array_diff_key($request->all(), $updateParams);
        UserPrivacy::validateProfileSettings($id, $settingParams);

        $this->eventRepository->updateEvent($context, $id, $updateParams);
        $this->privacyRepository->updateUserPrivacy($id, $settingParams);

        return $this->success(null, [], __p('event::phrase.setting_successfully_updated'));
    }
}
