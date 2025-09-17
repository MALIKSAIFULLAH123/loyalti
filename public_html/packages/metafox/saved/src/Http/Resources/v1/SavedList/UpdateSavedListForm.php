<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Saved\Models\SavedList as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateSavedListForm.
 * @property Model $resource
 */
class UpdateSavedListForm extends StoreSavedListForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function prepare(): void
    {
        $name    = null;
        $privacy = $this->resource->privacy;

        if ($this->resource instanceof Model) {
            $name = $this->resource->name;
        }

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $this
            ->title(__('saved::phrase.edit_collection'))
            ->action(url_utility()->makeApiUrl("saveditems-collection/{$this->resource->entityId()}"))
            ->asPut()
            ->setValue([
                'name'    => $name,
                'privacy' => $privacy,
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
                    __p('core::phrase.maximum_length_of_characters', ['length' => $maxCollectionNameLength])
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
            $privacyField
        );

        $footer = $this->addFooter();
        $footer->addFields(
            Builder::submit()
                ->label(__p('core::phrase.submit')),
            Builder::cancelButton(),
        );
    }
}
