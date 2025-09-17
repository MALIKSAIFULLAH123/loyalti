<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Music\Http\Requests\v1\Playlist\CreateFormRequest;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Policies\PlaylistPolicy;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StorePlaylistMobileForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorePlaylistMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, PlaylistRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();
        $this->setOwner($context);
        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            [
                'message'       => __p('music::web.you_have_reached_your_limit', ['entity_type' => Model::ENTITY_TYPE]),
                'messageFormat' => 'text',
            ]
        );
        policy_authorize(PlaylistPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'music_playlist.item_privacy');

        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $this->title(__p('music::phrase.add_new_playlist'))
            ->action(url_utility()->makeApiUrl('music/playlist'))
            ->asPost()
            ->setBackProps(__p('core::web.music'))
            ->setValue([
                'module_id'    => 'music',
                'privacy'      => $privacy,
                'useThumbnail' => true,
                'owner_id'     => $this->resource->owner_id,
            ]);
    }

    protected function initialize(): void
    {
        $basic                 = $this->addBasic();
        $minPlaylistNameLength = Settings::get(
            'music.music_playlist.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxPlaylistNameLength = Settings::get(
            'music.music_playlist.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );
        $privacyField = $this->buildPrivacyField()
            ->description(__p('music::phrase.control_who_can_see_this_playlist'));

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->marginNormal()
                ->label(__p('core::phrase.name'))
                ->placeholder(__p('music::phrase.fill_in_a_name_for_your_playlist'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxPlaylistNameLength]))
                ->maxLength($maxPlaylistNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minPlaylistNameLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minPlaylistNameLength]
                            )
                        )
                        ->maxLength(
                            $maxPlaylistNameLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minPlaylistNameLength,
                                'max' => $maxPlaylistNameLength,
                            ])
                        )
                ),
            $this->buildTextField(),
            Builder::singlePhoto('thumbnail')
                ->label(__p('music::phrase.cover_photo'))
                ->itemType('music_playlist')
                ->previewUrl($this->resource?->image_file_id ? $this->resource?->image : '')
                ->showWhen([
                    'or',
                    ['neq', 'file', null], ['truthy', 'useThumbnail'],
                ]),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            $privacyField,
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('description')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('music::phrase.add_some_description_to_your_playlist'));
        }

        return Builder::textArea('description')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('music::phrase.add_some_description_to_your_playlist'));
    }
}
