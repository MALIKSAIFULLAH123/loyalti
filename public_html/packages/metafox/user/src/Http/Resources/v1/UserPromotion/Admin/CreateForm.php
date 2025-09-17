<?php

namespace MetaFox\User\Http\Resources\v1\UserPromotion\Admin;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateForm.
 */
class CreateForm extends EditForm
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->config([
            'title'  => 'Add New Promotion',
            'action' => '/admincp/user/cancel/reason',
            'method' => 'POST',
        ]);
    }
}
