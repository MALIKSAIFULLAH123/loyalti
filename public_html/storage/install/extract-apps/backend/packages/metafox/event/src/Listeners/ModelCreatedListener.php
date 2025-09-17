<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

class ModelCreatedListener
{
    private UserPrivacyRepositoryInterface $privacyRepository;

    /**
     * @param UserPrivacyRepositoryInterface $privacyRepository
     */
    public function __construct(UserPrivacyRepositoryInterface $privacyRepository)
    {
        $this->privacyRepository = $privacyRepository;
    }

    /**
     * @param  mixed $model
     * @return void
     */
    public function handle($model): void
    {
        if ($model instanceof Event) {
            $this->handleDefaultPrivacies($model);
        }
    }

    private function handleDefaultPrivacies(Event $model): void
    {
        $profileSettings = $this->privacyRepository->getProfileSettings($model->entityId());
        $defaultPrivacies = collect($profileSettings)->pluck('value', 'var_name')->toArray();
        if (!empty($defaultPrivacies)) {
            $this->privacyRepository->updateUserPrivacy($model->entityId(), $defaultPrivacies);
        }
    }
}
