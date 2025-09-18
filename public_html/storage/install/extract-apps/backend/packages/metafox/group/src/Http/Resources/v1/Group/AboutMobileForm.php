<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Facades\Settings;

/**
 * Class AboutForm.
 * @property Model $resource
 * @deprecated Mobile version than v1.9
 */
class AboutMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = [
            'text_description' => $this->resource->groupText ? parse_output()->parseItemDescription($this->resource->groupText->text_parsed, false, true) : '',
        ];

        if (null !== $this->resource->location_name) {
            Arr::set($values, 'location', [
                'address'      => $this->resource->location_name,
                'lat'          => $this->resource->location_latitude,
                'lng'          => $this->resource->location_longitude,
                'full_address' => $this->resource->location_address,
            ]);
        }

        $this->asPut()
            ->title(__p('group::phrase.about_group'))
            ->action("group/{$this->resource->entityId()}")
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            $this->buildTextDescriptionField(),
            Builder::location('location')
                ->label(__p('core::phrase.location'))
                ->placeholder(__p('group::phrase.this_group_location')),
        );
    }

    protected function buildTextDescriptionField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text_description')
                ->label(__p('core::phrase.description'));
        }

        return Builder::textArea('text_description')
            ->label(__p('core::phrase.description'));
    }
}
