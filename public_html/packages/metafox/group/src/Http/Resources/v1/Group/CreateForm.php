<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\Yup\Yup;

/**
 * Class CreateForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateForm extends AbstractForm
{
    public const MAX_TEXT_LENGTH = 3000;

    public function boot(): void
    {
        $context = user();

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(GroupPolicy::class, 'create', $context);
    }

    protected function prepare(): void
    {
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = resolve(CategoryRepositoryInterface::class);
        $categoryDefault    = $categoryRepository->getCategoryDefault();
        $values             = [];

        if ($categoryDefault?->is_active == Category::IS_ACTIVE) {
            $values = ['category_id' => $categoryDefault->entityId()];
        }

        $this->asPost()
            ->setBackProps(__p('core::web.groups'))
            ->title(__p('group::phrase.create_group'))
            ->action(Model::API_URL)
            ->navigationConfirmation([
                'title'          => __p('core::web.leave_page'),
                'message'        => __p('group::phrase.if_you_leave_now_your_group_wont_be_created_and_your_progress_wont_be_saved'),
                'negativeButton' => [
                    'label' => __p('group::phrase.stay_on_page'),
                ],
                'positiveButton' => [
                    'label' => __p('core::web.leave'),
                ],
            ])->setValue($values);
    }

    protected function initialize(): void
    {
        $basic              = $this->addBasic();
        $minGroupNameLength = Settings::get('group.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxGroupNameLength = Settings::get('group.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $basic->addFields(
            Builder::text('name')
                ->required()
                ->label(__p('group::phrase.group_name'))
                ->placeholder(__p('group::phrase.fill_in_a_name_for_your_group'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxGroupNameLength]))
                ->maxLength($maxGroupNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength($minGroupNameLength)
                        ->maxLength($maxGroupNameLength)
                        ->matches(Regex::getRegexSetting('display_name'), Regex::getRegexErrorMessage('display_name'))
                ),
            $this->buildTextField(),
            Builder::category('category_id')
                ->label(__p('core::phrase.category'))
                ->multiple(false)
                ->required()
                ->setRepository(CategoryRepositoryInterface::class)
                ->valueType('number')
                ->sx(['width' => 275])
                ->yup(
                    Yup::number()->required()
                ),
        );

        if (app_active('metafox/friend')) {
            $basic->addField(
                Builder::friendPicker('users')
                    ->label(__p('friend::phrase.invite_friends'))
                    ->placeholder(__p('friend::phrase.invite_friends'))
                    ->multiple(true)
                    ->endpoint(url_utility()->makeApiUrl('friend'))
            );
        }

        $basic->addField(
            Builder::choice('reg_method')
                ->required()
                ->label(__p('group::phrase.group_privacy'))
                ->options($this->getRegOptions())
                ->sx(['width' => 275])
                ->yup(
                    Yup::string()->required()
                ),
        );

        $this->addDefaultFooter();
    }

    /**
     * @return array<int, mixed>
     */
    protected function getRegOptions(): array
    {
        return [
            [
                'value'       => PrivacyTypeHandler::PUBLIC,
                'label'       => __p('group::phrase.public'),
                'description' => __p('group::phrase.anyone_can_see_the_group_its_members_and_their_posts'),
            ],
            [
                'value'       => PrivacyTypeHandler::CLOSED,
                'label'       => __p('group::phrase.closed'),
                'description' => __p('group::phrase.anyone_can_find_the_group_and_see_who_s_in_it_only_members_can_see_posts'),
            ],
            [
                'value'       => PrivacyTypeHandler::SECRET,
                'label'       => __p('group::phrase.secret'),
                'description' => __p('group::phrase.only_members_can_find_the_group_and_see_posts'),
            ],
        ];
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->maxLength(self::MAX_TEXT_LENGTH)
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('core::phrase.add_some_description_to_your_type', ['type' => __p_type_key('group')]));
        }

        return Builder::textArea('text')
            ->maxLength(self::MAX_TEXT_LENGTH)
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('core::phrase.add_some_description_to_your_type', ['type' => __p_type_key('group')]));
    }
}
