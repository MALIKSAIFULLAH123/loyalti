<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Support\Facade\Page;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxFileType;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\Yup\Yup;

class CreateMobileForm extends AbstractForm
{
    public const MAX_TITLE_LENGTH = 64;
    public const MAX_TEXT_LENGTH  = 3000;

    public function boot(): void
    {
        $context = user();

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);

        policy_authorize(PagePolicy::class, 'create', $context);
    }

    protected function prepare(): void
    {
        $categoryRepository = resolve(PageCategoryRepositoryInterface::class);
        $categoryDefault    = $categoryRepository->getCategoryDefault();
        $values             = [];

        if ($categoryDefault?->is_active == Category::IS_ACTIVE) {
            $values = ['category_id' => $categoryDefault->entityId()];
        }

        $this->title(__('page::phrase.create_new_page'))
            ->asPost()
            ->action(url_utility()->makeApiUrl('page'))
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $variant           = 'standard';
        $minPageNameLength = Settings::get('page.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxPageNameLength = Settings::get('page.maximum_name_length', self::MAX_TITLE_LENGTH);

        $basic = $this->addBasic();
        $basic->addFields(
            $this->getFieldAvatarUpload(),
            Builder::text('name')
                ->required()
                ->variant($variant)
                ->minLength($minPageNameLength)
                ->maxLength($maxPageNameLength)
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxPageNameLength]))
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('page::phrase.fill_in_a_name_for_your_page'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minPageNameLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minPageNameLength]
                            )
                        )
                        ->maxLength(
                            $maxPageNameLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minPageNameLength,
                                'max' => $maxPageNameLength,
                            ])
                        )
                        ->matches(
                            Regex::getRegexSetting('display_name'),
                            Regex::getRegexErrorMessage('display_name')
                        )
                ),
            $this->getDescriptionField(),
            Builder::text('external_link')
                ->label(__p('core::phrase.external_link'))
                ->placeholder(__p('core::phrase.external_link'))
                ->yup(Yup::string()->url(__p('page::validation.external_link_must_be_a_valid_url'))),
            Builder::category('category_id')
                ->variant($variant)
                ->required()
                ->multiple(false)
                ->valueType('number')
                ->label(__p('core::phrase.category'))
                ->setRepository(PageCategoryRepositoryInterface::class),
        );
    }

    protected function getDescriptionField(): AbstractField
    {
        $field = match (Page::allowHtmlOnDescription()) {
            false   => Builder::textArea('text'),
            default => Builder::richTextEditor('text'),
        };

        return $field->variant('standard')
            ->asMultiLine()
            ->maxLength(self::MAX_TEXT_LENGTH)
            ->placeholder(__p('core::phrase.add_some_description_to_your_type', ['type' => 'page']))
            ->label(__p('core::phrase.description'));
    }

    protected function getFieldAvatarUpload(): AbstractField
    {
        return Builder::avatarUpload('image')
            ->accept(file_type()->getMimeTypeFromType(MetaFoxFileType::PHOTO_TYPE, false))
            ->label(__p('user::phrase.profile_image'))
            ->placeholder(__p('user::phrase.profile_image'))
            ->description(__p('user::phrase.profile_image_desc'))
            ->yup(Yup::object()->addProperty('base64', Yup::string()));
    }
}
