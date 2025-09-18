<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * Class PermissionMobileForm.
 * @property Model             $resource
 * @property array<int, mixed> $settings the list of privacy settings
 */
class PermissionMobileForm extends AbstractForm
{
    /**
     * @var array<int, mixed>
     */
    private array $settings;

    public function boot(
        GroupRepositoryInterface $repository,
        UserPrivacyRepositoryInterface $privacyRepository,
        ?int $id = null
    ): void {
        $this->resource = $repository->find($id);
        $this->settings = $privacyRepository->getProfileSettings($id);
    }

    protected function prepare(): void
    {
        $value = collect($this->settings)->pluck('value', 'var_name');

        $this
            ->title(__('group::phrase.group_permissions'))
            ->action('group/privacy/' . $this->resource->entityId())
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        foreach ($this->settings as $setting) {
            $basic->addField(
                Builder::radioGroup($setting['var_name'])
                    ->label($setting['phrase'])
                    ->options($setting['options']),
            );
        }
    }
}
