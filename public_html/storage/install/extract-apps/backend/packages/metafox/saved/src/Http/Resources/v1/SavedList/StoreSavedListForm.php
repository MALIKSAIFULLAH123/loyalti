<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Saved\Models\SavedList as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreSavedListForm.
 * @property ?Model $resource
 */
class StoreSavedListForm extends AbstractForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        $this
            ->title(__('saved::phrase.new_collection'))
            ->action(url_utility()->makeApiUrl('saveditems-collection'))
            ->asPost()
            ->setValue([
                'privacy' => MetaFoxPrivacy::ONLY_ME,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $maxCollectionNameLength = Settings::get('saved.maximum_name_length', 64);
        $privacyField            = $this->buildPrivacyField();

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->label(__p('core::phrase.name'))
                ->placeholder(
                    __p('saved::phrase.fill_name_for_collection')
                )
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxCollectionNameLength]))
                ->maxLength($maxCollectionNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->maxLength(
                            $maxCollectionNameLength,
                            __p('core::phrase.maximum_length_of_characters', ['length' => $maxCollectionNameLength])
                        )
                ),
            $privacyField,
        );

        $this->addDefaultFooter(false);
    }

    protected function buildPrivacyField(): AbstractField
    {
        return Builder::hidden('privacy');
    }
}
