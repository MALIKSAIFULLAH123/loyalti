<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteForm.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('invite')
            ->acceptPageParams(['q', 'status', 'start_date', 'end_date'])
            ->setValue([
                'status'     => Browse::VIEW_ALL,
                'start_date' => null,
                'end_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::text('q')
                ->label(__p('invite::phrase.emails_phone_numbers'))
                ->placeholder(__p('invite::phrase.emails_phone_numbers'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->marginDense(),
            Builder::choice('status')
                ->fullWidth()
                ->sizeSmall()
                ->marginDense()
                ->label(__p('invite::phrase.status'))
                ->sxFieldWrapper($this->getResponsiveSx())
                ->options(InviteFacade::getStatusOptions()),
            Builder::date('start_date')
                ->label(__p('core::web.from'))
                ->placeholder(__p('core::web.from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->yup(
                    Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                ),
            Builder::date('end_date')
                ->label(__p('core::phrase.to_label'))
                ->placeholder(__p('core::phrase.to_label'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->yup(Yup::date()
                    ->nullable()
                    ->min(['ref' => 'start_date'])
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                    ->setError('min', __p('invite::phrase.the_end_time_should_be_greater_than_the_start_time'))
                    ->setError(
                        'minDate',
                        __p('invite::phrase.the_end_time_should_be_greater_than_the_current_time')
                    )),
            Builder::submit()
                ->label(__p('core::phrase.search')),
            Builder::clearSearchForm()
                ->forAdminSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->sizeMedium(),
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
