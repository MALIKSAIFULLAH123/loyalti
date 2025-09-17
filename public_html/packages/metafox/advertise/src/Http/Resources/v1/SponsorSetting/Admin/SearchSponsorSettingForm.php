<?php

namespace MetaFox\Advertise\Http\Resources\v1\SponsorSetting\Admin;

use Illuminate\Http\Request;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\FormField;
use MetaFox\Platform\UserRole;
use MetaFox\Form\Builder as Builder;
use MetaFox\Advertise\Models\Sponsor as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSponsorForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchSponsorSettingForm extends AbstractForm
{
    private int $roleId = UserRole::NORMAL_USER_ID;

    public function boot(Request $request): void
    {
        $this->roleId = (int) $request->get('role_id', UserRole::NORMAL_USER_ID);
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

    /**
     * @return array<int, mixed>
     */
    protected function getRoleOptions(): array
    {
        $options = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        $disallowedRoleIds = [UserRole::SUPER_ADMIN_USER_ID, UserRole::GUEST_USER_ID, UserRole::BANNED_USER_ID];

        return array_values(array_filter($options, function ($option) use ($disallowedRoleIds) {
            return !in_array($option['value'], $disallowedRoleIds);
        }));
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
                    ->options($this->getRoleOptions())
            );
    }
}
