<?php

namespace MetaFox\Group\Http\Resources\v1\Invite;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Group\Models\Invite as Model;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('group-invite')
            ->acceptPageParams(['view', 'status', 'q', 'created_from', 'created_to'])
            ->setValue([
                'view'         => ViewScope::VIEW_ALL,
                'status'       => null,
                'created_from' => null,
                'created_to'   => null,
            ])
            ->asGet();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::text('q')
                    ->label(__p('group::phrase.search_group_invite_placeholder'))
                    ->placeholder(__p('group::phrase.search_group_invite_placeholder'))
                    ->fullWidth()
                    ->sizeSmall()
                    ->sxFieldWrapper(['maxWidth' => 220])
                    ->marginDense(),
                Builder::choice('view')
                    ->label(__p('core::phrase.role'))
                    ->options(ViewScope::getViewOptions())
                    ->forAdminSearchForm()
                    ->disableClearable()
                    ->sxFieldWrapper($this->getResponsiveSx()),
                Builder::choice('status')
                    ->label(__p('core::web.status'))
                    ->options(StatusScope::getStatusOptions())
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx()),
                Builder::date('created_from')
                    ->label(__p('group::phrase.created_from_label'))
                    ->startOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('group::phrase.created_from_label')]))
                    ),
                Builder::date('created_to')
                    ->label(__p('group::phrase.created_to_label'))
                    ->endOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(Yup::date()
                        ->nullable()
                        ->min(['ref' => 'created_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('group::phrase.created_to_label')]))
                        ->setError('min', __p('group::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDateTime',
                            __p('group::phrase.the_end_time_should_be_greater_than_the_current_time')
                        )),
                Builder::submit()
                    ->label(__p('core::phrase.submit'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->marginDense(),
            );
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width'    => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
