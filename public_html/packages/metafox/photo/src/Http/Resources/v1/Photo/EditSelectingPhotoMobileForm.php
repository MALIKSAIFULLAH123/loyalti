<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Support\Traits\MatureFieldTrait;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class EditSelectingPhotoMobileForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class EditSelectingPhotoMobileForm extends AbstractForm
{
    use MatureFieldTrait;

    protected function prepare(): void
    {
        $this->title(__p('photo::phrase.edit_photo'));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->description(__p(
                    'core::phrase.maximum_length_of_characters',
                    ['length' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH]
                ))
                ->yup(
                    Yup::string()->required(__('validation.this_field_is_a_required_field'))
                ),
            Builder::textArea('text')
                ->required(false)
                ->returnKeyType('default')
                ->placeholder(__p('photo::phrase.add_some_content_to_your_photo'))
                ->label(__p('core::phrase.description')),
            $this->buildMobileMatureField(),
        );
    }
}
