<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\TourGuide\Models\Step as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateStepForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateStepForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('tourguide::phrase.update_step'))
            ->action(apiUrl('admin.tourguide.step.update', ['step' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'title_var'        => Language::getPhraseValues($this->resource->title_var),
                'desc_var'         => Language::getPhraseValues($this->resource->desc_var),
                'is_active'        => (int) $this->resource->is_active,
                'delay'            => $this->resource->delay,
                'background_color' => $this->resource->background_color,
                'font_color'       => $this->resource->font_color,
            ]);
    }

    protected function initialize(): void
    {
        $this->addHeader()
            ->component('FormHeader')
            ->sx([
                'borderBottom'      => '1px solid',
                'borderBottomColor' => 'theme => theme.palette.divider',
                'pb'                => 1,
                'mb'                => 1,
            ]);

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::translatableText('title_var')
                ->required()
                ->label(__p('tourguide::phrase.step_title'))
                ->sx(['mb' => 0])
                ->buildFields(),
            Builder::translatableText('desc_var')
                ->required()
                ->asTextEditor()
                ->label(__p('tourguide::phrase.step_desc'))
                ->sx(['mb' => 0])
                ->buildFields(),
            Builder::text('delay')
                ->required()
                ->asNumber()
                ->label(__p('tourguide::phrase.step_delay_time'))
                ->marginDense()
                ->yup(Yup::number()->unint()->required()),
            Builder::colorPicker('background_color')
                ->marginDense()
                ->label(__p('tourguide::phrase.step_custom_background')),
            Builder::colorPicker('font_color')
                ->marginDense()
                ->label(__p('tourguide::phrase.step_font_color')),
            Builder::switch('is_active')
                ->marginDense()
                ->label(__p('tourguide::phrase.is_active')),
        );

        $this->addDefaultFooter();
    }
}
