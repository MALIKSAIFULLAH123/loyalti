<?php

namespace MetaFox\Featured\Http\Resources\v1\Package\Admin;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Featured\Models\Package as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchPackageForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/featured/package')
            ->acceptPageParams(['title', 'duration_period', 'pricing', 'status'])
            ->asGet()
            ->submitAction('@formAdmin/search/SUBMIT');
    }

    protected function initialize(): void
    {
        $this->addBasic([
            'sx' => [
                'flexFlow'   => 'wrap',
                'alignItems' => 'flex-start',
            ],
        ])
        ->asHorizontal()
        ->sxContainer(['alignItems' => 'unset'])
        ->addFields(
            Builder::text('title')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.title'))
                ->maxLength(255),
            Builder::choice('duration_period')
                ->forAdminSearchForm()
                ->label(__p('featured::admin.duration'))
                ->options(Feature::getDurationOptionsForSearch()),
            Builder::choice('pricing')
                ->forAdminSearchForm()
                ->label(__p('featured::web.pricing'))
                ->options(Feature::getPricingOptions()),
            Builder::choice('status')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.status'))
                ->options(Feature::getStatusOptions()),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }
}
