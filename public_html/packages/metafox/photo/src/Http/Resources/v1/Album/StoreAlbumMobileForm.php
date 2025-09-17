<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Photo\Http\Requests\v1\Album\CreateFormRequest;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Policies\AlbumPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Support\Facades\Album as FacadesAlbum;
use MetaFox\Platform\Contracts\HasPrivacyMember;
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
 * Class StoreAlbumMobileForm.
 * @property Model $resource
 *
 * @driverType form-mobile
 * @driverName photo.album.store
 */
class StoreAlbumMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, AlbumRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();
        $params  = Arr::add($params, 'user_id', $context->entityId());
        $params  = Arr::add($params, 'user_type', $context->entityType());

        $ownerId = Arr::get($params, 'owner_id');
        if ($ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;
            $this->setOwner($owner);
        }

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            ['messageFormat' => 'text', 'message' => __p('photo::phrase.quota_control_photo_album_invalid')]
        );
        policy_authorize(AlbumPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'photo_album.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $this->title(__p('photo::phrase.create_new_photo_album'))
            ->action(url_utility()->makeApiUrl('photo-album'))
            ->asPost()
            ->setValue([
                'privacy'  => $privacy,
                'owner_id' => $this->resource->owner_id ?? 0,
                'text'     => '', // set default value to prevent dirty
                'title'    => '',
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic          = $this->addBasic();
        $minLength      = Settings::get('photo.album.minimum_name_length');
        $maxLength      = Settings::get('photo.album.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $isDefaultAlbum = $this->resource instanceof Model && FacadesAlbum::isDefaultAlbum($this->resource->album_type);

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->returnKeyType('next')
                ->disabled($isDefaultAlbum)
                ->marginNormal()
                ->maxLength($maxLength)
                ->label(__p('core::phrase.name'))
                ->placeholder(__p('photo::phrase.enter_album_name'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxLength]))
                ->yup(
                    Yup::string()
                        ->minLength($minLength)
                        ->maxLength($maxLength)
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            $this->buildTextField(),
            $this->buildPrivacyFieldForAlbum(),
            Builder::hidden('owner_id'),
        );
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildPrivacyFieldForAlbum(): AbstractField
    {
        $context        = user();

        if (!$context->hasPermissionTo('photo_album.set_privacy')) {
            return Builder::hidden('privacy');
        }

        if ($this->owner instanceof HasPrivacyMember) {
            return Builder::hidden('privacy');
        }

        return $this->buildPrivacyField()
            ->label(__p('photo::phrase.album_privacy'))
            ->description(__p('photo::phrase.description_for_privacy_field'));
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(false)
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('photo::phrase.enter_album_description'));
        }

        return Builder::textArea('text')
            ->required(false)
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('photo::phrase.enter_album_description'));
    }
}
