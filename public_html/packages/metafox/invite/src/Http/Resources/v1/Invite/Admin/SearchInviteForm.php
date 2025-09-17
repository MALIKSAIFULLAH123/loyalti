<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteForm.
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('invite')
            ->acceptPageParams(['q', 'status', 'start_date', 'end_date', 'user_name', 'owner_name', 'sort', 'sort_type'])
            ->setValue([
                'status'     => Invite::STATUS_COMPLETED,
                'start_date' => null,
                'end_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::text('user_name')
                ->label(__p('invite::phrase.inviter'))
                ->placeholder(__p('invite::phrase.inviter'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('q')
                ->label(__p('invite::phrase.emails_phone_numbers'))
                ->placeholder(__p('invite::phrase.emails_phone_numbers'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::choice('status')
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense()
                ->label(__p('invite::phrase.status'))
                ->options(InviteFacade::getStatusOptions()),
            Builder::text('owner_name')
                ->label(__p('invite::phrase.invitee'))
                ->placeholder(__p('invite::phrase.invitee'))
                ->fullWidth()
                ->sizeSmall()
                ->showWhen(['neq', 'status', Invite::STATUS_PENDING])
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::submit()
                ->marginDense()
                ->label(__p('core::phrase.search')),
            Builder::clearSearchForm()
                ->marginDense()
                ->label(__p('core::phrase.reset'))
                ->align('center')->excludeFields(['status']),
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
