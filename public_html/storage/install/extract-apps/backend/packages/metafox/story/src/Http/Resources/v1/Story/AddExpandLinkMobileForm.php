<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class AddExpandLinkForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class AddExpandLinkMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/')
            ->title(__p('story::phrase.add_link'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('expand_link')
                ->label(null)
                ->placeholder(__p('story::phrase.add_link'))
                ->description(__p('story::web.add_link_description'))
                ->sxFieldWrapper([
                    'mt' => 1,
                ])
                ->yup(Yup::string()->nullable()->url(__p('validation.url', ['attribute' => __p('story::phrase.add_link')]))),
        );


        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->sizeSmall()
                    ->label(__p('core::web.add')),
                Builder::cancelButton()
                    ->sizeSmall(),
            );
    }
}
