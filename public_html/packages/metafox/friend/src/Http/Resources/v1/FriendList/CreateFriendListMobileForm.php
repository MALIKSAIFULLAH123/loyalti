<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Friend\Models\FriendList as Model;
use MetaFox\Friend\Policies\FriendListPolicy;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateFriendListForm.
 * @property ?Model $resource
 */
class CreateFriendListMobileForm extends AbstractForm
{
    public function boot(FriendListRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();

        policy_authorize(FriendListPolicy::class, 'create', $context);
    }

    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('friend::phrase.add_new_list'))
            ->action('friend/list');
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $maxFriendNameLength = Settings::get('friend.maximum_name_length', 64);

        $basic->addFields(
            Builder::typography()
                ->label(__p('friend::phrase.description_create_friend_list')),
            Builder::text('name')->required()
                ->sizeLarge()
                ->variant('standard')
                ->placeholder(__p('friend::phrase.fill_a_list_name'))
                ->label(__p('core::phrase.name'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxFriendNameLength]))
                ->maxLength($maxFriendNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->maxLength(
                            $maxFriendNameLength,
                            __p('core::phrase.maximum_length_of_characters', ['length' => $maxFriendNameLength])
                        )
                ),
            $this->buildFriendPickerField(),
        );
    }

    protected function buildFriendPickerField(): ?AbstractField
    {
        $context = user();

        if (!$context->hasPermissionTo('friend_list.update')) {
            return null;
        }

        if (version_compare(MetaFox::getApiVersion(), 'v1.6', '<')) {
            return null;
        }

        return Builder::friendPicker('users')
            ->multiple()
            ->apiEndpoint(url_utility()->makeApiUrl('friend'))
            ->placeholder(__p('friend::phrase.search_for_friends'));
    }
}
