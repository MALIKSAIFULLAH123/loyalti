<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * @preload 1
 */
class MassEmailMobileForm extends MassEmailForm
{
    protected function initialize(): void
    {
        $basic      = $this->addBasic();
        $isDisabled = $this->isDisableSubmitField();

        $this->addHeader(['showRightHeader' => !$isDisabled])
            ->component('FormHeader');

        $basic->addFields(
            Builder::text('subject')
                ->label(__p('event::phrase.subject'))
                ->required()
                ->placeholder(__p('event::phrase.subject'))
                ->yup(Yup::string()
                    ->required()),
            $this->buildTextField(),
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->label(__p('event::phrase.text'))
                ->placeholder(__p('event::phrase.text'));
        }

        return Builder::textArea('text')
            ->label(__p('event::phrase.text'))
            ->placeholder(__p('event::phrase.text'));
    }
}
