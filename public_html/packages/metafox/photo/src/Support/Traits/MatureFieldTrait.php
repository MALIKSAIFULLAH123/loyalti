<?php

namespace MetaFox\Photo\Support\Traits;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Support\Facades\Photo as PhotoFacade;
use MetaFox\Yup\Yup;

/**
 * Trait MatureFieldTrait.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait MatureFieldTrait
{
    public function buildMatureField(): ?AbstractField
    {
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

        if (!$context->hasPermissionTo('photo.add_mature_image')) {
            return null;
        }

        if ($this->isResourcePhotoAndRestricted()) {
            return null;
        }

        return $builder::radioGroup('mature')
            ->label(__p('photo::phrase.mature_content'))
            ->options(PhotoFacade::getMatureContentOptions())
            ->yup(Yup::string());
    }

    private function isResourcePhotoAndRestricted(): bool
    {
        return property_exists($this, 'resource')
            && $this->resource instanceof Photo
            && ($this->resource->is_profile_photo || $this->resource->is_cover_photo);
    }
}
