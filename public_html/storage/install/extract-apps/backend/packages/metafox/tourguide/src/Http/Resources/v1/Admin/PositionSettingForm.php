<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Admin;

use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Platform\Facades\Settings;
use MetaFox\TourGuide\Form\Html\DraggableBox;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PositionSettingForm.
 * @property Model $resource
 */
class PositionSettingForm extends Form
{
    protected function prepare(): void
    {
        $this
            ->action('admincp/setting/tourguide/position')
            ->asPost()
            ->setValue([
                'tourguide' => [
                    'tour_guide_button' => Settings::get('tourguide.tour_guide_button'),
                ],
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                (new DraggableBox())
                ->name('tourguide.tour_guide_button')
                ->setAttribute('height', 600)
            );

        $this->addDefaultFooter(true);
    }
}
