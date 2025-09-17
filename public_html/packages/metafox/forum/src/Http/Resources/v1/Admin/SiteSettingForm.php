<?php

namespace MetaFox\Forum\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Forum\Support\Facades\ForumThread;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'forum';

        $vars = [
            'forum.minimum_name_length',
            'forum.maximum_name_length',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->asPost()
            ->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $minTitleLength = ForumThread::getDefaultMinimumTitleLength();

        $maxTitleLength = ForumThread::getDefaultMaximumTitleLength();

        $basic->addFields(
            Builder::text('forum.minimum_name_length')
                ->label(__p('forum::phrase.minimum_name_length'))
                ->placeholder(__p('forum::phrase.minimum_name_length'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required()
                        ->int()
                        ->min(
                            $minTitleLength,
                            __p('forum::validation.admincp.minimum_name_length', ['number' => $minTitleLength])
                        )
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('forum.maximum_name_length')
                ->label(__p('forum::phrase.maximum_name_length'))
                ->placeholder(__p('forum::phrase.maximum_name_length'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required()
                        ->int()
                        ->when(
                            Yup::when('minimum_name_length')
                            ->is('$exists')
                            ->then(Yup::number()->min(['ref' => 'minimum_name_length']))
                        )
                        ->max(
                            $maxTitleLength,
                            __p('forum::validation.admincp.maximum_name_length', ['number' => $maxTitleLength])
                        )
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }
}
