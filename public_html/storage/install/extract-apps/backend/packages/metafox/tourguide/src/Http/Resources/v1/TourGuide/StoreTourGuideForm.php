<?php

namespace MetaFox\TourGuide\Http\Resources\v1\TourGuide;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\TourGuide\Models\TourGuide as Model;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\TourGuide\Policies\TourGuidePolicy;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;
use MetaFox\TourGuide\Supports\Browse\Scopes\PrivacyScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreTourGuideForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreTourGuideForm extends AbstractForm
{
    protected TourGuideRepositoryInterface $repository;

    public function boot(TourGuideRepositoryInterface $repository): void
    {
        $this->repository = $repository;

        policy_authorize(TourGuidePolicy::class, 'create', user());
    }

    protected function prepare(): void
    {
        $this->title(__p('tourguide::phrase.new_tour_guide'))
            ->action('tour-guide')
            ->asPost()
            ->setValue([
                'is_auto' => 1,
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
                ->options(PrivacyScope::getPrivacyOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
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
