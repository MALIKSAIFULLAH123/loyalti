<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\Saved as Model;
use MetaFox\Saved\Policies\SavedPolicy;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Models\UserEntity;

/**
 * Class SavedItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SavedItem extends JsonResource
{
    use HasStatistic;

    private ?int $collectionId = null;

    protected function listRepository(): SavedListRepositoryInterface
    {
        return resolve(SavedListRepositoryInterface::class);
    }

    /**
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    protected function getStatistic(): array
    {
        $savedLists = $this->listRepository()->filterSavedListByUser(
            user(),
            $this->resource->savedLists
        );

        return [
            'total_collection' => $savedLists->count(),
        ];
    }

    public function setCollectionId(?int $collectionId): self
    {
        $this->collectionId = $collectionId;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $item         = $this->resource?->item;
        $context      = user();
        $title        = '';
        $image        = null;
        $link         = null;
        $router       = null;
        $owner        = null;
        $itemTypeName = null;
        $totalPhoto   = 0;
        $photoPhrase  = '';

        if ($item instanceof HasSavedItem) {
            /**
             * @TODO Refactor this code in version 5.1.8.2
             */
            $savedData = $item->toSavedItem();

            if (method_exists($item, 'toSavedItemWithContext')) {
                $savedData = $item->toSavedItemWithContext($context);
            }

            if (!empty($savedData)) {
                $title = $this->handleTitle($item, $savedData['title']); //todo: substring
                $image = $savedData['image'];
                /** @var UserEntity $owner */
                $owner        = $savedData['user'];
                $itemTypeName = $savedData['item_type_name'];
                $totalPhoto   = $savedData['total_photo'];
                $link         = $savedData['link'];
                $router       = $savedData['router'] ?? null;

                if ($totalPhoto > 0) {
                    $photoPhrase = '1 ' . __p('photo::phrase.photo');
                }

                if ($totalPhoto > 1) {
                    $photoPhrase = $totalPhoto . ' ' . __p('photo::phrase.photos');
                }

                if (empty($title) && $this->resource->item_type == 'photo_set') {
                    $title = $photoPhrase;
                }
            }
        }
        $defaultCollection   = $this->resource->default_collection;
        $belongToCollection  = false;
        $currentCollectionId = null;

        if ($defaultCollection != null) {
            $belongToCollection = true;
        }

        if ($this->collectionId != null) {
            $defaultCollection   = resolve(SavedListRepositoryInterface::class)->find($this->collectionId);
            $belongToCollection  = true;
            $currentCollectionId = $this->collectionId;
        }

        return [
            'id'                      => $this->resource->entityId(),
            'module_name'             => 'saved',
            'resource_name'           => $this->resource->entityType(),
            'user'                    => ResourceGate::user($this->resource->userEntity),
            'owner'                   => new UserEntityDetail($owner),
            'is_saved'                => true,
            'item_type'               => $this->resource->itemType(),
            'item_id'                 => $this->resource->itemId(),
            'title'                   => htmlspecialchars_decode($title),
            'additional_information'  => null,
            'image'                   => $image,
            'is_opened'               => $this->resource->isOpened($context, $this->collectionId),
            'item_type_name'          => $itemTypeName,
            'total_photo'             => $totalPhoto,
            'photo_phrase'            => $photoPhrase,
            'saved_id'                => $this->resource->entityId(),
            'belong_to_collection'    => $belongToCollection,
            'default_collection_name' => $defaultCollection?->name,
            'default_collection_id'   => $defaultCollection?->entityId(),
            'creation_date'           => $this->resource->created_at,
            'modification_date'       => $this->resource->updated_at,
            'list_id'                 => $this->collectionId,
            'link'                    => $link,
            'router'                  => $router,
            'extra'                   => $this->getCustomExtra(),
            'privacy'                 => MetaFoxPrivacy::EVERYONE,
            'statistic'               => $this->getStatistic(),
            'collection_ids'          => $this->resource->collection_ids,
            'url'                     => $link ? url_utility()->makeApiFullUrl($link) : null,
            'embed_object'            => ResourceGate::asEmbed($this->resource->item),
            'current_collection_id'   => $currentCollectionId,
        ];
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getCustomExtra(): array
    {
        $context = user();
        /** @var SavedPolicy $policy */
        $policy = PolicyGate::getPolicyFor(Saved::class);

        $extraPerms = [
            ACL::CAN_VIEW         => $policy->view($context, $this->resource),
            ACL::CAN_DELETE       => $policy->delete($context, $this->resource),
            ACL::CAN_ADD          => $policy->create($context),
            ACL::CAN_EDIT         => $policy->update($context, $this->resource),
            ACL::CAN_SHARE        => false,
            'is_collection_owner' => false,
            'can_remove'          => $this->resource->userId() == $context->entityId(),
            'can_add_to_list'     => $policy->addItemToCollection($context, $this->resource),
        ];

        if ($this->collectionId) {
            $collection                        = resolve(SavedListRepositoryInterface::class)->find($this->collectionId);
            $extraPerms['is_collection_owner'] = $collection->userId() == $context->entityId();
            $extraPerms['can_remove']          = $extraPerms['is_collection_owner'] || $this->resource->userId() == $context->entityId();
            $extraPerms['can_add_to_list']     = $policy->addItemToCollection($context, $this->resource);
        }

        return $extraPerms;
    }

    private function handleTitle(Content $item, string $title): string
    {
        app('events')->dispatch('core.parse_content', [$item, &$title, [
            'parse_url' => false,
        ]]);

        return $title;
    }
}
