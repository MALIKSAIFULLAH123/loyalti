<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\UserGender as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchTourGuideForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchTourGuideForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('tourguide/tour-guide')
            ->acceptPageParams(['q', 'url', 'user_name', 'is_active']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->label(__p('tourguide::phrase.guide')),
            Builder::text('url')
                ->forAdminSearchForm()
                ->label(__p('tourguide::phrase.page_url')),
            Builder::text('user_name')
                ->label(__p('tourguide::phrase.created_by'))
                ->placeholder(__p('tourguide::phrase.created_by'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::choice('is_active')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.is_active'))
                ->options($this->getActiveOptions()),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getActiveOptions(): array
    {
        return [
            ['label' => __p('core::phrase.yes'), 'value' => 1],
            ['label' => __p('core::phrase.no'), 'value' => 0],
        ];
    }
}
