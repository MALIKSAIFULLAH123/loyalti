<?php

namespace MetaFox\Hashtag\Http\Resources\v1\Hashtag\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Html\Text;
use MetaFox\Hashtag\Models\Hashtag as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class TagSettingHashtagForm.
 * @property ?Model $resource
 */
class TagSettingHashtagForm extends AbstractForm
{
    protected function prepare(): void
    {
        //Todo: implement later
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
