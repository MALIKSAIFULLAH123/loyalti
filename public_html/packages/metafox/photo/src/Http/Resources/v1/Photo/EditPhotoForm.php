<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Traits\MatureFieldTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Yup\Yup;

/**
 * Class EditPhotoForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class EditPhotoForm extends AbstractForm
{
    use PrivacyFieldTrait;
    use MatureFieldTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(PhotoRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();

        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);

        policy_authorize(PhotoPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $privacy = $this->getPrivacy();

        $text = $this->getDescription();

        $this->title(__p('photo::phrase.edit_photo'))
            ->submitAction('@photo/uploadMultiPhotos/submit')
            ->setBackProps(__p('core::web.photos'))
            ->asPut()
            ->action(url_utility()->makeApiUrl("/photo/{$this->resource->entityId()}"))
            ->setValue([
                'title'      => $this->resource->title,
                'text'       => $text,
                'privacy'    => $privacy,
                'album'      => $this->resource->album_id,
                'mature'     => $this->resource->mature,
                'categories' => $this->resource->categories->pluck('id')->toArray(),
                'owner_id'   => $this->resource->owner_id,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->marginNormal()
                ->label(__p('core::phrase.title'))
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->description(__p(
                    'core::phrase.maximum_length_of_characters',
                    ['length' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH]
                ))
                ->yup(
                    Yup::string()->required(__('validation.this_field_is_a_required_field'))
                ),
            Builder::textArea('text')
                ->required(false)
                ->returnKeyType('default')
                ->placeholder(__p('photo::phrase.add_some_content_to_your_photo'))
                ->label(__p('core::phrase.description')),
            $this->buildMatureField(),
        );

        $this->addAlbumField($basic);
        $basic->addField(
            $this->buildPrivacyFieldForPhoto()
        );

        if (Settings::get('photo.allow_photo_category_selection', true)) {
            $basic->addFields(
                Builder::category()
                    ->sizeLarge()
                    ->setRepository(CategoryRepositoryInterface::class)
                    ->setSelectedCategories($this->resource->categories)
            );
        }

        $this->addDefaultFooter(true);
    }

    protected function buildPrivacyFieldForPhoto(): AbstractField
    {
        $defaultAlbums = resolve(AlbumRepositoryInterface::class)->getDefaultUserAlbums($this->resource->ownerId());
        $albumId       = $this->resource->album_id;

        if (in_array($albumId, $defaultAlbums->pluck('id')->toArray())) {
            return Builder::hidden('privacy');
        }

        return $this->buildPrivacyField()
            ->label(__p('photo::phrase.photo_privacy'))
            ->description(__p('photo::phrase.photo_privacy_description'))
            ->showWhen(['falsy', 'album'])
            ->fullWidth();
    }

    protected function addAlbumField(Section $basic): void
    {
        if ($this->canUpdateAlbum()) {
            $basic->addField(
                Builder::album('album')
                    ->multiple(false)
                    ->sizeLarge()
                    ->fullWidth()
                    ->label(__p('photo::phrase.photo_album'))
                    ->setOwner($this->resource->owner)
                    ->setUser(user())
                    ->setRepository(AlbumRepositoryInterface::class)
                    ->yup(
                        Yup::number()
                            ->nullable()
                    )
            );

            return;
        }

        $basic->addField(
            Builder::hidden('album')
        );
    }

    protected function getDescription(): string
    {
        $totalItem = 0;

        if (null !== $this->resource->group) {
            $totalItem = $this->resource->group->total_item;
        }

        $photoText = MetaFoxConstant::EMPTY_STRING;

        if (null !== $this->resource->photoInfo) {
            $photoText = $this->resource->photoInfo->text_parsed;
        }

        if ($totalItem == 1) {
            if (null === $this->resource->content) {
                $photoText = $this->resource->group->content;
            }
        }

        return $photoText ?? '';
    }

    /**
     * @return array<int>|string
     */
    protected function getPrivacy(): mixed
    {
        $privacy = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];

            if (is_array($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        return $privacy;
    }

    protected function canUpdateAlbum(): bool
    {
        /** @var PhotoPolicy $policy */
        $policy = PolicyGate::getPolicyFor(Photo::class);

        if (null === $policy) {
            return true;
        }

        return $policy->updateAlbum(user(), $this->resource);
    }
}
