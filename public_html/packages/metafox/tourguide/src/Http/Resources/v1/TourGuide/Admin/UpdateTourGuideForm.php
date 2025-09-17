<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\TourGuide\Models\TourGuide as Model;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\TourGuide\Supports\Browse\Scopes\PrivacyScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateTourGuideForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateTourGuideForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('tourguide::phrase.update_tour_guide'))
            ->action("admincp/tourguide/tour-guide/{$this->resource->entityId()}")
            ->asPut()
            ->setValue([
                'name'      => $this->resource->name,
                'url'       => $this->resource->url,
                'privacy'   => $this->resource->privacy,
                'is_active' => (int) $this->resource->is_active,
                'is_auto'   => (int) $this->resource->is_auto,
            ]);
    }

    protected function initialize(): void
    {
        $maxLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->label(__p('tourguide::phrase.tour_guide_name'))
                ->maxLength($maxLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->maxLength($maxLength)
                ),
            Builder::choice('privacy')
                ->required()
                ->disableClearable()
                ->label(__p('tourguide::phrase.user_permissions'))
                ->options(PrivacyScope::getPrivacyOptions()),
            Builder::text('url')
                ->required()
                ->label(__p('tourguide::phrase.page_url'))
                ->warning(__p('tourguide::phrase.warning_edit_url_tour_guide'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::switch('is_active')
                ->marginDense()
                ->label(__p('tourguide::phrase.is_active')),
            Builder::switch('is_auto')
                ->marginDense()
                ->label(__p('tourguide::phrase.enable_autorun')),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('tourguide::phrase.save_and_continue'))
            );
    }
}
