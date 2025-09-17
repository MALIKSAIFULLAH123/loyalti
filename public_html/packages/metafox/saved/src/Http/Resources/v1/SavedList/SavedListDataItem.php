<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedItem;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedListData as Model;
use MetaFox\Saved\Policies\SavedPolicy;

/**
 * Class SavedListDataItem.
 * @property Model $resource
 */
class SavedListDataItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $savedItems = $this->resource->savedItems;

        if ($savedItems == null) {
            return [];
        }

        $resource   = (new SavedItem($savedItems))
            ->setCollectionId($this->resource->list_id)
            ->toArray($request);
        $resource['extra'] = array_merge($resource['extra'], $this->getExtra());

        return $resource;
    }

    protected function getExtra(): array
    {
        $context = user();

        /** @var SavedPolicy $policy */
        $policy                  = PolicyGate::getPolicyFor(Saved::class);
        $canRemoveFromCollection = $policy->removeItemFromCollection(
            $context,
            $this->resource->savedLists,
            $this->resource->savedItems
        );

        return [
            'can_remove' => $canRemoveFromCollection,
            'is_owner'   => $this->resource->savedItems->userId() == $context->entityId(),
        ];
    }
}
