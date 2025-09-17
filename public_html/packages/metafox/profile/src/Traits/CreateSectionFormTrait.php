<?php

namespace MetaFox\Profile\Traits;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Repositories\ProfileRepositoryInterface;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Yup\Yup;

trait CreateSectionFormTrait
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/profile/section')
            ->asPost()
            ->setValue($this->getValues());
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->yup(
                        Yup::string()
                            ->required()
                            ->matches(MetaFoxConstant::RESOURCE_IDENTIFIER_REGEX, __p('validation.alpha_underscore_lower_only', [
                                'attribute' => '${path}',
                            ]))
                    ),
                Builder::translatableText('label')
                    ->label(__p('core::phrase.label'))
                    ->required()
                    ->buildFields(),
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active')),
            );

        $this->addDefaultFooter();
    }

    protected function getValues(): array
    {
        $profile = $this->getCustomProfile();
        $values  = [
            'is_active' => MetaFoxConstant::IS_ACTIVE,
        ];

        if ($this->isEdit()) {
            Arr::set($values, 'name', $this->resource->name);
            Arr::set($values, 'label', $this->getPhraseValues(sprintf('profile::phrase.%s_label', $this->resource->name)));
        }

        if ($profile instanceof Profile) {
            Arr::set($values, 'profile_id', $profile->entityId());
        }

        return $values;
    }

    public function getCustomProfile(): \Illuminate\Database\Eloquent\Model
    {
        return $this->profileRepository()->getModel()->newQuery()
            ->where('profile_type', $this->getUserType())
            ->first();
    }

    protected function profileRepository(): ProfileRepositoryInterface
    {
        return resolve(ProfileRepositoryInterface::class);
    }

    public function getUserType(): string
    {
        return CustomFieldSupport::SECTION_TYPE_USER;
    }

    public function getPhraseValues(string $keyPhrase): array
    {
        $values = Language::getPhraseValues($keyPhrase);

        return array_map(function ($value) {
            return htmlspecialchars_decode($value);
        }, $values);
    }

    public function isEdit(): bool
    {
        return false;
    }
}
