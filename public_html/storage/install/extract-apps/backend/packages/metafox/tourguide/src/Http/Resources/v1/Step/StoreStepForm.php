<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Models\Step as Model;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;
use MetaFox\TourGuide\Supports\Constants;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreStepForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreStepForm extends AbstractForm
{
    protected TourGuideRepositoryInterface $tourGuideRepository;
    protected TourGuide $tourGuide;

    public function boot(TourGuideRepositoryInterface $tourGuideRepository): void
    {
        $this->tourGuideRepository = $tourGuideRepository;

        $tourGuideId = (int) request()->get('tour_guide_id');

        $this->tourGuide = $this->tourGuideRepository->find($tourGuideId);
    }

    protected function prepare(): void
    {
        $this->title(__p('tourguide::phrase.step'))
            ->action('tour-guide/step')
            ->asPost()
            ->setValue([
                'tour_guide_id' => $this->tourGuide->entityId(),
                'page_name'     => request()->get('page_name'),
                'is_active'     => 1,
                'delay'         => Constants::DEFAULT_TOUR_GUIDE_DELAY_TIME,
                'is_completed'  => 0,
            ]);
    }

    protected function initialize(): void
    {
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

        $this->addFooter()
            ->sx([
                'mt'            => '0 !important',
                'display'       => 'flex',
                'flexDirection' => 'row-reverse',
            ])
            ->addFields(
                Builder::submit()
                    ->label(__p('tourguide::phrase.save_and_continue'))
                    ->sxFieldWrapper([
                        'ml' => '8px !important',
                        'mr' => '0 !important',
                    ]),
                Builder::submit('is_completed')
                    ->label(__p('tourguide::phrase.complete'))
                    ->setValue(1)
                    ->sxFieldWrapper([
                        'ml' => '8px !important',
                        'mr' => '0 !important',
                    ]),
                Builder::cancelButton()
                    ->sxFieldWrapper([
                        'ml' => '8px !important',
                        'mr' => '0 !important',
                    ]),
            );
    }
}
