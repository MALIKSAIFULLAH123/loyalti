<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * Class PermissionPageMobileForm.
 * @property Model $resource
 */
class PermissionPageMobileForm extends AbstractForm
{
    /**
     * @var array<int, mixed>
     */
    private array $settings;

    public function boot(PageRepositoryInterface $repository, UserPrivacyRepositoryInterface $privacyRepository, ?int $id): void
    {
        $this->resource = $repository->find($id);
        $this->settings = $privacyRepository->getProfileSettings($id);
    }

    protected function prepare(): void
    {
        $value = collect($this->settings)->pluck('value', 'var_name');

        $this
            ->title(__('page::phrase.page_permissions'))
            ->action('page/privacy/' . $this->resource->entityId())
            ->secondAction('updatePagePermission')
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
                    ->options($setting['options'])
            );
        }
    }
}
