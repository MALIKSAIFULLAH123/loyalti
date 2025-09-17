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
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Traits\MatureFieldTrait;
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
 *
 * @driverType form-mobile
 * @driverName photo.photo.update
 */
class UpdatePhotoMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;
    use MatureFieldTrait;

    private bool $canUpdateAlbum = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(PhotoRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);

        policy_authorize(PhotoPolicy::class, 'update', $context, $this->resource);

        $this->canUpdateAlbum = policy_check(PhotoPolicy::class, 'updateAlbum', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $privacy = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $text = $this->getDescription();

        $this->title(__p('photo::phrase.edit_photo'))
            ->asPut()
            ->action(url_utility()->makeApiUrl("/photo/{$this->resource->entityId()}"))
            ->setValue([
                'title'      => $this->resource->title,
                'text'       => $text,
                'privacy'    => $privacy,
                'mature'     => $this->resource->mature,
                'album'      => $this->resource->album_id,
                'categories' => $this->resource->categories->pluck('id')->toArray(),
                'owner_id'   => $this->resource->owner_id,
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->addFields(
                $this->buildAlbumField(),
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
                        Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                    ),
            );

        if (Settings::get('photo.allow_photo_category_selection', true)) {
            $basic->addFields(
                Builder::category()
                    ->sizeLarge()
                    ->setRepository(CategoryRepositoryInterface::class)
                    ->setSelectedCategories($this->resource->categories)
                    ->width(275)
            );
        }

        $basic->addFields(
            $this->buildTextField(),
            $this->buildMobileMatureField(),
            $this->buildPrivacyFieldForPhoto(),
            Builder::hidden('owner_id'),
        );
    }

    protected function buildPrivacyFieldForPhoto(): AbstractField
    {
        $defaultAlbums = resolve(AlbumRepositoryInterface::class)->getDefaultUserAlbums($this->resource->ownerId());
        $albumId       = $this->resource->album_id;

        if (in_array($albumId, $defaultAlbums->pluck('id')->toArray())) {
            return Builder::hidden('privacy');
        }

        if ($albumId != 0) {
            return Builder::hidden('privacy');
        }

        return $this->buildPrivacyField()
            ->fullWidth(false)
            ->label(__p('photo::phrase.photo_privacy'))
            ->description(__p('photo::phrase.photo_privacy_description'));
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

    protected function buildAlbumField(): AbstractField
    {
        if ($this->canUpdateAlbum) {
            return Builder::album('album')
                    ->multiple(false)
                    ->sizeLarge()
                    ->fullWidth()
                    ->label(__p('photo::phrase.photo_album'))
                    ->setOwner($this->resource->owner)
                    ->setUser(user())
                    ->setRepository(AlbumRepositoryInterface::class);
        }

        return Builder::hidden('album');
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(false)
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'));
        }

        return Builder::textArea('text')
            ->required(false)
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'));
    }
}
