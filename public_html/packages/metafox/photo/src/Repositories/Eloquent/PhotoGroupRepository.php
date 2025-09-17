<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Policies\PhotoGroupPolicy;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class AlbumRepository.
 *
 * @mixin Builder
 * @property PhotoGroup $model
 * @method   PhotoGroup getModel()
 */
class PhotoGroupRepository extends AbstractRepository implements PhotoGroupRepositoryInterface
{
    use CheckModeratorSettingTrait;

    public function model(): string
    {
        return PhotoGroup::class;
    }

    public function viewPhotoGroup(User $context, int $id): PhotoGroup
    {
        $photoSet = $this->withUserMorphTypeActiveScope()->with(['userEntity', 'ownerEntity', 'items', 'approvedItems'])->find($id);

        policy_authorize(PhotoGroupPolicy::class, 'view', $context, $photoSet);

        return $photoSet;
    }

    public function forceContentForGlobalSearch(array $files, ?string $content): array
    {
        if (count($files) != 1) {
            return $files;
        }

        foreach ($files as $key => $file) {
            $text = Arr::get($file, 'text');

            if (MetaFoxConstant::EMPTY_STRING == $text) {
                $text = null;
            }

            if (null !== $text) {
                continue;
            }

            if (null === $text) {
                $file['searchable_text'] = $content;
            }

            $files[$key] = $file;
        }

        return $files;
    }

    public function updateGlobalSearchForSingleMedia(PhotoGroup $group, ?string $text, int $total, ?array $oldFiles = null): void
    {
        $oldItems = $group->items;

        if (null === $oldItems) {
            return;
        }

        if ($total == 0) {
            return;
        }

        $checked = [];

        if (is_array($oldFiles)) {
            foreach ($oldFiles as $oldFile) {
                $checked[$oldFile['type']][] = $oldFile['id'];
            }
        }

        $first = $oldItems->first(function ($value) use ($checked) {
            if (!count($checked)) {
                return true;
            }

            $ids = Arr::get($checked, $value->itemType());

            if (null === $ids) {
                return true;
            }

            return !in_array($value->itemId(), $ids);
        });

        if (null === $first) {
            return;
        }

        $detail = $first->detail;

        if (!$detail instanceof HasGlobalSearch) {
            return;
        }

        if (null !== $detail->content) {
            return;
        }

        $searchable = $detail->toSearchable();

        if (null === $searchable) {
            return;
        }

        $content = null;

        if ($total == 1) {
            $content = $text;
        }

        $searchable = array_merge($searchable, [
            'text' => $content ?? MetaFoxConstant::EMPTY_STRING,
        ]);

        app('events')->dispatch('search.update_search_text', [$detail->entityType(), $detail->entityId(), $searchable], true);
    }

    public function handleContent(array $attributes, string $field = 'content'): array
    {
        if (Arr::has($attributes, $field)) {
            $content = Arr::get($attributes, $field);

            if (null == $content) {
                Arr::set($attributes, $field, MetaFoxConstant::EMPTY_STRING);
            }
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function updateApprovedStatus(?PhotoGroup $group): bool
    {
        if (null === $group) {
            return false;
        }

        if ($group->items()->count() < 1) {
            return false;
        }

        $isApproved = $this->assertApproveStatus($group);
        $success    = $group->update(['is_approved' => $isApproved]);

        if ($group->isApproved() && $group->wasChanged('is_approved') && $group->pendingItems()->count() == 0) {
            app('events')->dispatch('notification.new_post_to_follower', [$group->user, $group]);
        }

        return $success;
    }

    private function assertApproveStatus(PhotoGroup $group): bool
    {
        $owner = $group->owner;
        if ($owner?->hasPendingMode() && $owner?->isPendingMode()) {
            return $this->checkModeratorSetting($group->user, $owner, 'approve_or_deny_post');
        }

        return $group->approvedItems()->count() > 0;
    }

    /**
     * @param PhotoGroup|null $group
     *
     * @return bool
     */
    public function cleanUpGroup(?PhotoGroup $group): bool
    {
        if (!$group instanceof PhotoGroup) {
            return true;
        }

        if (!$group->items()->count()) {
            $group->delete();

            return true;
        }

        return false;
    }

    public function deleteUserPhotoGroups(User $user): void
    {
        $groups = $this->getModel()
            ->newModelQuery()
            ->where(function (Builder $subQuery) use ($user) {
                $subQuery
                    ->where('owner_id', '=', $user->entityId())
                    ->orWhere('user_id', '=', $user->entityId());
            })
            ->get()
            ->collect();
        $ids    = $groups->pluck('id')->toArray();
        $query  = PhotoGroupItem::query()->whereIn('group_id', $ids);

        foreach ($query->lazy() as $item) {
            if (!$item instanceof PhotoGroupItem) {
                continue;
            }

            $item->detail()->delete();
        }

        $groups->each->delete();
    }

    public function viewItems(User $context, PhotoGroup $group, array $attributes = []): Paginator
    {
        $page = $this->resolvePageForDetailView($context, $group, $attributes);

        $query = $this->getBasicItemBuilder($group, $attributes);

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        return $query->paginate($limit, ['photo_group_items.*'], 'page', $page);
    }

    /**
     * @param User       $context
     * @param PhotoGroup $group
     * @param array      $attributes
     *
     * @return int
     * @throws AuthorizationException
     */
    protected function resolvePageForDetailView(User $context, PhotoGroup $group, array $attributes = []): int
    {
        $mediaId = Arr::get($attributes, 'media_id');

        if (!is_numeric($mediaId)) {
            return Arr::get($attributes, 'page', 1);
        }

        /**
         * @var PhotoGroupItem $item
         */
        $item = PhotoGroupItem::query()
            ->where([
                'group_id' => $group->entityId(),
                'item_id'  => $mediaId,
            ])
            ->firstOrFail();

        $media = $item->item;

        if (null === $media) {
            throw new ModelNotFoundException();
        }

        if (!$context->can('view', [$media, $media])) {
            throw new AuthorizationException();
        }

        if ($group->isApproved() && !$media->isApproved()) {
            throw new AuthorizationException();
        }

        $query = $this->getBasicItemBuilder($group, $attributes);

        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_ASC);

        match ($sortType) {
            Browse::SORT_TYPE_DESC => $query->where('photo_group_items.id', '>=', $item->entityId()),
            default                => $query->where('photo_group_items.id', '<=', $item->entityId()),
        };

        $total = $query->count();

        $limit = (int) Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (!$total || $total <= $limit) {
            return 1;
        }

        $page = $total / $limit;

        $surplus = $total % $limit;

        if (0 === $surplus) {
            return $page;
        }

        return $page + 1;
    }

    protected function getBasicItemBuilder(PhotoGroup $group, array $attributes = []): Builder
    {
        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_ASC);

        $query = PhotoGroupItem::query()
            ->where('photo_group_items.group_id', '=', $group->entityId());

        if ($group->isApproved()) {
            $query->where('photo_group_items.is_approved', '=', 1);
        }

        $query->orderBy('photo_group_items.id', $sortType);

        return $query;
    }
}
