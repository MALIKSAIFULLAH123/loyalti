<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1\Type\Admin;

use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class BuiltinAdminSearchForm.
 *
 * Generic search form class for admincp.
 * @driverName ignore
 */
class SearchTypeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->acceptPageParams(['q', 'module_id'])
            ->action('/feed/type');
    }

    protected function initialize(): void
    {
        $this->acceptPageParams(['q', 'module_id']);

        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('module_id')
                    ->label(__p('app::phrase.app'))
                    ->options($this->getAppOptions())
                    ->forAdminSearchForm(),
                Builder::choice('is_active')
                    ->label(__p('activity::admin.enabled'))
                    ->forAdminSearchForm()
                    ->options($this->getYesNoOptions()),
                Builder::choice('can_create_feed')
                    ->label(__p('activity::phrase.activity_type_can_create_feed'))
                    ->forAdminSearchForm()
                    ->options($this->getYesNoOptions()),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    protected function getAppOptions(): array
    {
        $moduleIds = array_column(resolve(TypeManager::class)->getTypes(), 'module_id');

        return array_values(array_filter(resolve(PackageRepositoryInterface::class)->getPackageOptions(), function ($package) use ($moduleIds) {
            return in_array($package['value'], $moduleIds);
        }));
    }

    /**
     * @return array<int, mixed>
     */
    protected function getYesNoOptions(): array
    {
        return [
            ['label' => __p('core::phrase.yes'), 'value' => 1],
            ['label' => __p('core::phrase.no'), 'value' => 0],
        ];
    }
}
