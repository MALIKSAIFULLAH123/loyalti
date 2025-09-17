<?php

namespace MetaFox\Video\Traits;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Video\Support\Facade\Video as VideoFacade;
use MetaFox\Yup\Yup;

trait MatureFieldTrait
{
    public function buildMatureField(bool $isEdit = false): ?AbstractField
    {
        if (!$isEdit) {
            return null;
        }

        $field = $this->buildMatureFieldCommon(Builder::class);

        if (!$field instanceof AbstractField) {
            return $field;
        }

        $field->showWhen(['neqeqeq', 'extra.can_edit_mature', false]);

        return $field;
    }

    public function buildMobileMatureField(): ?AbstractField
    {
        return $this->buildMatureFieldCommon(MobileBuilder::class);
    }

    private function buildMatureFieldCommon($builder): ?AbstractField
    {
        $context = user();

        if (!$context->hasPermissionTo('video.add_mature_video')) {
            return null;
        }

        return $builder::radioGroup('mature')
            ->label(__p('video::phrase.mature_content'))
            ->options(VideoFacade::getMatureContentOptions())
            ->yup(Yup::string());
    }
}
