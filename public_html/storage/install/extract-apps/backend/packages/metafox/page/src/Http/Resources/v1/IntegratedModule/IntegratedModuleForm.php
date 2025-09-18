<?php

namespace MetaFox\Page\Http\Resources\v1\IntegratedModule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Page\Models\IntegratedModule;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;

/**
 * Class IntegratedModuleForm.
 * @property int               $id       user id
 * @property array<int, mixed> $settings the list of privacy settings
 */
class IntegratedModuleForm extends AbstractForm
{
    private Collection $data;
    private ?int $id;

    /**
     * @param  IntegratedModuleRepositoryInterface $repository
     * @param  PageRepositoryInterface             $pageRepository
     * @param  int|null                            $id
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(IntegratedModuleRepositoryInterface $repository, PageRepositoryInterface $pageRepository, ?int $id = null): void
    {
        $this->id   = $id;
        $this->data = $repository->getModules($id);
        $page       = $pageRepository->find($id);
        policy_authorize(PagePolicy::class, 'manageMenuSetting', user(), $page);
    }

    protected function prepare(): void
    {
        $value = [];

        foreach ($this->data as $menu) {
            $value[$menu['name']]       = $menu['is_active'];
        }

        $this->title(__('page::phrase.page_menu_settings'))
            ->action(url_utility()->makeApiUrl("page-integrated/$this->id/"))
            ->asPut()
            ->setValue($value)
            ->secondAction('nothing')
            ->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->component(MetaFoxForm::COMPONENT_SORTABLE)
            ->setAttribute('orderAction', 'page/orderingItem');

        foreach ($this->data as $menu) {
            $isDisable = false;
            if (in_array($menu['name'], IntegratedModule::TAB_NAME_DEFAULTS)) {
                $isDisable = true;
            }

            $basic->addFields(
                Builder::switch($menu['name'])
                    ->disabled($isDisable)
                    ->label(__p($menu['label']))
            );
        }
    }
}
