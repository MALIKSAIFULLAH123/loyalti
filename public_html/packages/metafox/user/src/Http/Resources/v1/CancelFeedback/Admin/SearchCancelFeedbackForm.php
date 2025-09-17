<?php

namespace MetaFox\User\Http\Resources\v1\CancelFeedback\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\CancelFeedback as Model;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchCancelFeedbackForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchCancelFeedbackForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(apiUrl('admin.user.feedback.index'))
            ->asGet()
            ->acceptPageParams(['q', 'role'])
            ->setValue([
                'role' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('role')
                    ->forAdminSearchForm()
                    ->multiple(false)
                    ->label(__p('authorization::phrase.role'))
                    ->disableClearable()
                    ->options($this->getRoleOptions()),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    protected function getRoleOptions(): array
    {
        $default = [
            [
                'label' => __p('core::phrase.all'),
                'value' => 0,
            ],
        ];
        $roles = resolve(RoleRepositoryInterface::class)->getRoleOptionsWithout([UserRole::SUPER_ADMIN_USER]);

        return array_merge($default, $roles);
    }
}
