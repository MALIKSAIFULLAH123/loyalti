<?php

namespace MetaFox\Photo\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Photo\Contracts\AlbumContract;
use MetaFox\Photo\Models\Album as ModelsAlbum;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Class Album.
 */
class Album implements AlbumContract
{
    public function __construct(protected AlbumRepositoryInterface $repository) {}

    /**
     * @return array
     */
    public function getDefaultTypes(): array
    {
        return [
            ModelsAlbum::COVER_ALBUM,
            ModelsAlbum::PROFILE_ALBUM,
            ModelsAlbum::TIMELINE_ALBUM,
        ];
    }

    /**
     * @param int|null $value
     * @return bool
     */
    public function isDefaultAlbum(?int $value): bool
    {
        if (null === $value) {
            return false;
        }

        return in_array($value, $this->getDefaultTypes());
    }

    /**
     * @param ModelsAlbum $album
     *
     * @return string
     */
    public function getDefaultAlbumTitle(ModelsAlbum $album): string
    {
        $name        = $album->name;
        $ownerEntity = $album->ownerEntity;
        if ($ownerEntity instanceof UserEntity) {
            $name = __p($name, ['full_name' => $ownerEntity->name]);
        }

        return $name;
    }

    /**
     * @param User   $context
     * @param string $userType
     * @param int    $userId
     * @return mixed|void
     */
    public function chunkingTrashedAlbums(User $context, string $userType, int $userId)
    {
        $this->repository->chunkingTrashedAlbums($context, $userType, $userId);
    }

    /**
     * @param ModelsAlbum $album
     * @param int|null    $limit
     * @return Collection|null
     */
    public function getMediaItems(ModelsAlbum $album, ?int $limit = 4, bool $forApproved = true): ?Collection
    {
        if ($forApproved) {
            return $this->getApprovedMediaItems($album, $limit);
        }

        if (null === $limit) {
            return $album->items;
        }

        /* @link \MetaFox\Photo\Support\LoadMissingAlbumItems::after */
        return LoadReduce::remember(sprintf('photo_album::items(%s)', $album->id), function () use ($album, $limit) {
            if (!$album->relationLoaded('items')) {
                $album->loadMissing([
                    'items' => function (HasMany $query) use ($limit) {
                        $query->limit($limit);
                    },
                ]);

                return $album->items;
            }

            return $album->items->take($limit);
        });
    }

    protected function getApprovedMediaItems(ModelsAlbum $album, ?int $limit = 4): ?Collection
    {
        if (null === $limit) {
            return $album->items->filter(function (AlbumItem $item) {
                return $item->is_approved;
            })->values();
        }

        /* @link \MetaFox\Photo\Support\LoadMissingAlbumApprovedItems::after */
        return LoadReduce::remember(sprintf('photo_album::approved_items(%s)', $album->id),
            function () use ($album, $limit) {
                if (!$album->relationLoaded('items')) {
                    $album->loadMissing([
                        'items' => function (HasMany $query) use ($limit) {
                            $query->where('is_approved', '=', 1)
                                ->limit($limit);
                        },
                    ]);

                    return $album->items;
                }

                return $album->items->filter(function (AlbumItem $item) {
                    return $item->is_approved;
                })->values()->take($limit);
            }
        );
    }
}
