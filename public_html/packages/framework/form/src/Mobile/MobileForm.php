<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Section;

abstract class MobileForm extends AbstractForm
{
    protected function initializeFlatten(): void
    {
        $this->initialize();
    }

    protected function getBasicFields(Section $section): void
    {

    }

    protected function getBottomSheetFields(Section $section): void
    {

    }

    protected function getSearchFields(): AbstractField
    {
        return Builder::text('q')
            ->forBottomSheetForm('SFSearchBox')
            ->delayTime(200)
            ->className('mb2');
    }

    protected function getSearchFieldsFlatten(): AbstractField
    {
        return Builder::text('q')
            ->forBottomSheetForm('SFSearchBox')
            ->delayTime(200)
            ->className('mb2')
            ->showWhen([
                'eq',
                'mobile_page_name',
                'search',
            ]);
    }

    protected function getClearSearchFieldsFlatten(): ClearSearch
    {
        return Builder::clearSearch()
            ->setComponent(MetaFoxForm::COMPONENT_RESET_SEARCH)
            ->label(__p('core::phrase.reset'));
    }
}
