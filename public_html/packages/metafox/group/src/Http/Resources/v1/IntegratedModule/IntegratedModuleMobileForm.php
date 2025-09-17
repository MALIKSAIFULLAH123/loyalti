<?php

namespace MetaFox\Group\Http\Resources\v1\IntegratedModule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Group\Support\Facades\Group;

/**
 * Class IntegratedModuleMobileForm.
 * @property int               $id       user id
 * @property array<int, mixed> $settings the list of privacy settings
 * @property Model             $resource
 */
class IntegratedModuleMobileForm extends AbstractForm
{
    private Collection $data;
    private ?int       $id;

    /**
     * @param IntegratedModuleRepositoryInterface $repository
     * @param GroupRepositoryInterface            $groupRepository
     * @param int|null                            $id
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(
        IntegratedModuleRepositoryInterface $repository,
        GroupRepositoryInterface            $groupRepository,
        ?int                                $id = null
    ): void
    {
        $this->id       = $id;
        $this->data     = $repository->getModules($id);
        $this->resource = $groupRepository->find($id);
        policy_authorize(GroupPolicy::class, 'manageMenuSetting', user(), $this->resource);
    }

    protected function prepare(): void
    {
        $value = [];

        foreach ($this->data as $menu) {
            $value[$menu['name']] = $menu['is_active'];
        }

        $this->title(__('group::phrase.group_menu_settings'))
            ->action(url_utility()->makeApiUrl("group-integrated/$this->id/"))
            ->asPut()
            ->setValue($value)
            ->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        foreach ($this->data as $menu) {
            $isDisable = false;
            if (in_array($menu['name'], Group::getTabNameDefaults($this->resource))) {
                $isDisable = true;
            }

            $basic->addFields(
                Builder::switch($menu['name'])
                    ->disabled($isDisable)
                    ->marginNone()
                    ->label(__p($menu['label']))
            );
        }
    }
}
