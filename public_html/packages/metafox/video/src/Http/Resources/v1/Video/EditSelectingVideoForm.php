<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Http\Resources\v1\Video;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Traits\MatureFieldTrait;
use MetaFox\Yup\Yup;

/**
 * Class EditSelectingVideoForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class EditSelectingVideoForm extends AbstractForm
{
    use MatureFieldTrait;

    protected function prepare(): void
    {
        $this->title(__p('video::phrase.edit_video_title'));
    }

    protected function initialize(): void
    {
        $minVideoNameLength = Settings::get('video.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxVideoNameLength = Settings::get('video.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->maxLength($maxVideoNameLength)
                ->description(__p(
                    'core::phrase.maximum_length_of_characters',
                    ['length' => $maxVideoNameLength]
                ))
                ->yup(
                    Yup::string()->required(__('validation.this_field_is_a_required_field'))
                        ->minLength($minVideoNameLength)
                        ->maxLength($maxVideoNameLength)
                ),
            $this->buildTextField(),
            $this->buildMatureField(true),
        );

        $this->addDefaultFooter(true);
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(false)
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('video::phrase.add_some_content_to_your_video'));
        }

        return Builder::textArea('text')
            ->required(false)
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('video::phrase.add_some_content_to_your_video'));
    }
}
