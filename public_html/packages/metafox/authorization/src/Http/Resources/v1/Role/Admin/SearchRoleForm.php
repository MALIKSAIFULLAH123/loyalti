<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use MetaFox\Authorization\Models\Role as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchRoleForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName user.user_role.search
 */
class SearchRoleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/authorization/role')
            ->acceptPageParams(['q'])
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->marginDense();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }
}
