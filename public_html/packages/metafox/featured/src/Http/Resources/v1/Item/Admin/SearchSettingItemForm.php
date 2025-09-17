<?php

namespace MetaFox\Featured\Http\Resources\v1\Item\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\UserRole;
use MetaFox\Form\Builder as Builder;
use MetaFox\Featured\Models\Item as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchSettingItemForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchSettingItemForm extends AbstractForm
{
    private int $roleId = UserRole::NORMAL_USER_ID;

    public function boot(Request $request): void
    {
        $this->roleId = (int) $request->get('role_id', UserRole::NORMAL_USER_ID);

        if (!in_array($this->roleId, Feature::getAllowedRole())) {
            throw new AuthorizationException(__p('phrase.permission_deny'));
        }
    }

    protected function prepare(): void
    {
        $this->noHeader()
            ->acceptPageParams(['role_id'])
            ->setValue([
                'role_id' => $this->roleId,
            ])
            ->submitOnValueChanged()
            ->submitAction('@formAdmin/search/SUBMIT');
    }

    protected function initialize(): void
    {
        $this->addSection([
            'name' => 'search',
        ])
            ->asHorizontal()
            ->marginDense()
            ->addFields(
                Builder::choice('role_id')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.role'))
                    ->disableClearable()
                    ->options(Feature::getAllowedRoleOptions())
            );
    }
}
