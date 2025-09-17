<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground\Admin;

use MetaFox\BackgroundStatus\Repositories\BgsBackgroundRepositoryInterface;
use MetaFox\BackgroundStatus\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\BackgroundStatus\Models\BgsBackground as Model;
use MetaFox\Yup\Yup;

/**
 * Class EditBackgroundForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class EditBackgroundForm extends AbstractForm
{
    public function boot(BgsBackgroundRepositoryInterface $repository, ?int $id = null): void
    {
        if ($id) {
            $this->resource = $repository->find($id);
        }
    }

    protected function prepare(): void
    {
        $values = ['text_color' => Support::WHITE_COLOR];

        if ($this->resource instanceof Model) {
            $values['text_color'] = $this->resource->text_color;
        }

        $this->title(__p('backgroundstatus::phrase.edit_background'))
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('text_color')
                ->label(__p('backgroundstatus::phrase.choose_text_color'))
                ->disableClearable()
                ->options(Support::getColorOptions()),
        );

        $this->addDefaultFooter(true);
    }
}
