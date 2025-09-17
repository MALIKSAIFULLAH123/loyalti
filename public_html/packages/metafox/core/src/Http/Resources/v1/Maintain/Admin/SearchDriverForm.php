<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Resources\v1\Maintain\Admin;

use MetaFox\Core\Constants;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class SearchDriverForm.
 * @driverName ignored
 */
class SearchDriverForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/core/drivers')
            ->setValue([
                'admin' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('type')
                    ->forAdminSearchForm()
                    ->options($this->getTypeOptions())
                    ->label(__p('core::phrase.type')),
                Builder::selectPackage('package_id')
                    ->forAdminSearchForm()
                    ->optional()
                    ->label(__p('core::phrase.package_name')),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    /**
     * @return array<array>>
     */
    private function getTypeOptions(): array
    {
        $types = Constants::AVAILABLE_DRIVER_TYPES;

        return  collect($types)->map(function ($value, $label) {
            return [
                'label' => $label,
                'value' => $value,
            ];
        })->values()
        ->toArray();
    }
}
