<?php

namespace MetaFox\Invite\Http\Resources\v1\InviteCode\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchInviteCodeForm.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInviteCodeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('invite/invite-code/browse')
            ->acceptPageParams(['q',])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::text('q')
                ->label(__p('core::phrase.search'))
                ->placeholder(__p('core::phrase.search_dot'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->marginDense(),
            Builder::submit()
                ->marginDense()
                ->label(__p('core::phrase.search')),
            Builder::clearSearchForm()
                ->marginDense()
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
