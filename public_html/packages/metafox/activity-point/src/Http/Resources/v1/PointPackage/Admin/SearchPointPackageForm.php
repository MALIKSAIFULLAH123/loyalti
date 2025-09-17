<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPointPackageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_package.search
 * @driverType form
 */
class SearchPointPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.manage_packages'))
            ->action('/activitypoint/package')
            ->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }
}
