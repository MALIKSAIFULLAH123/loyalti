<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PollSettingForm.
 * @property Model $resource
 */
class SiteSettingForm extends AbstractForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        $module = 'poll';
        $vars   = [
            'poll.is_image_required',
            'poll.minimum_name_length',
            'poll.maximum_name_length',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $basic             = $this->addBasic();
        $basic->addFields(
            Builder::switch('poll.is_image_required')
                ->label(__p('poll::phrase.is_image_required'))
                ->description(__p('poll::phrase.is_image_required_description')),
            Builder::text('poll.minimum_name_length')
                ->required()
                ->label(__p('poll::phrase.minimum_name_length'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('poll.maximum_name_length')
                ->required()
                ->label(__p('poll::phrase.maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->max($maximumNameLength)
                        ->when(
                            Yup::when('minimum_name_length')
                            ->is('$exists')
                            ->then(
                                Yup::number()
                                    ->min(['ref' => 'minimum_name_length', 1])
                                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                            )
                        )
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }
}
