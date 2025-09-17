<?php

namespace MetaFox\User\Http\Resources\v1\UserPromotion\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Html\CancelButton;
use MetaFox\Form\Html\Choice;
use MetaFox\Form\Html\Radio;
use MetaFox\Form\Html\Submit;
use MetaFox\Form\Html\Text;
use MetaFox\User\Models\UserPromotion as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditForm.
 */
class EditForm extends AbstractForm
{
    /**
     * @var Model
     */
    public $resource;

    protected function initialize(): void
    {
        $apiUrl = '/admincp/user/promotion';

        $this->config([
            'title'  => 'Edit Promotion',
            'action' => $apiUrl . $this->resource->id,
            'method' => 'PUT',
            'value'  => $this->resource->toArray(),
        ]);

        $info = $this->addSection(['name' => 'info']);

        $info->addField(new Choice([
            'name'          => 'user_group',
            'required'      => true,
            'returnKeyType' => 'next',
            'label'         => __p('core::phrase.role'),
            'options'       => [
                ['label' => 'Registered', 'value' => 4],
            ],
        ]));

        $info->addField(new Choice([
            'name'          => 'upgrade_user_group',
            'required'      => true,
            'returnKeyType' => 'next',
            'label'         => __p('user::phrase.upgrade_role'),
            'options'       => [
                ['label' => 'Staff', 'value' => 3],
            ],
        ]));
        $info->addField(new Text([
            'name'          => 'total_activity',
            'required'      => true,
            'returnKeyType' => 'next',
            'label'         => 'Total Activity',
            'placeholder'   => 'Total Activity',
        ]));

        $info->addField(new Text([
            'name'          => 'total_day',
            'required'      => true,
            'returnKeyType' => 'next',
            'label'         => 'Day Registered',
            'placeholder'   => 'Day Registered',
        ]));

        $info->addField(new Radio([
            'name'          => 'rule',
            'required'      => true,
            'returnKeyType' => 'next',
            'label'         => 'Rule Check',
            'placeholder'   => 'Rule to Check Promotion',
            'options'       => [
                [
                    'label' => 'Or, select "Or" for your users to be promoted by achieving at least one condition.',
                    'value' => 1,
                ],
                [
                    'label' => 'Rule to Check PromotionAnd, select "And" for your users to be promoted by achieving all conditions.',
                    'value' => 0,
                ],
            ],
        ]));

        /// keep footer

        $footer = $this->addSection(['name' => 'footer']);

        $footer->addFields(new CancelButton(), new Submit());
    }
}
