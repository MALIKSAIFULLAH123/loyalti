<?php

namespace MetaFox\Page\Http\Resources\v1\IntegratedModule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\IntegratedModule;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;

/**
 * Class IntegratedModuleMobileForm.
 * @property int               $id       user id
 * @property array<int, mixed> $settings the list of privacy settings
 */
class IntegratedModuleMobileForm extends AbstractForm
{
    private Collection $data;
    private ?int $id;

    /**
     * @param  IntegratedModuleRepositoryInterface $repository
     * @param  PageRepositoryInterface             $pageRepository
     * @param  int|null                            $id
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
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
            $value[$menu['name']] = $menu['is_active'];
        }

        $this->title(__('page::phrase.page_menu_settings'))
            ->action(url_utility()->makeApiUrl("page-integrated/$this->id/"))
            ->asPut()
            ->setValue($value)
            ->submitOnValueChanged();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        foreach ($this->data as $menu) {
            $isDisable = false;
            if (in_array($menu['name'], IntegratedModule::TAB_NAME_DEFAULTS)) {
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
