<?php

namespace MetaFox\User\Http\Resources\v1\UserPromotion\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Html\Text;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Models\UserPromotion as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateUserPromotionForm.
 * @property ?Model $resource
 */
class CreateUserPromotionForm extends AbstractForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        $this->config([
            'title'  => __p('core::phrase.edit'),
            'action' => 'blog/' . $this->resource->id,
            'method' => MetaFoxForm::METHOD_POST,
            'value'  => [
                //
            ],
        ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            new Text([
                'name'          => 'title',
                'required'      => true,
                'returnKeyType' => 'next',
                'label'         => 'Title',
                'placeholder'   => 'Fill in a title',
            ])
        );

        $this->addDefaultFooter();
    }
}
