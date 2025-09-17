<?php

namespace MetaFox\Platform\Support\Repository\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Interface HasSponsor.
 */
interface HasSponsor
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function unsponsor(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $sponsor
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function sponsor(User $context, int $id, int $sponsor): bool;

    /**
     * @param User       $context
     * @param int        $limit
     * @param array|null $loadedItemIds
     *
     * @return Collection
     */
    public function getRandomSponsoredItems(User $context, int $limit, ?array $loadedItemIds = null): Collection;

    /**
     * @param Content $content
     *
     * @return void
     */
    public function enableSponsor(Content $content): void;

    /**
     * @param Content $content
     *
     * @return void
     */
    public function disableSponsor(Content $content): void;

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isSponsor(Content $model): bool;

    /**
     * @param array<int>   $notInIds
     * @param int|null     $sponsorStart
     * @param array<mixed> $with
     *
     * @return Content | null
     */
    public function getSponsoredItem(array $notInIds, ?int $sponsorStart = null, array $with = []): ?Content;

    /**
     * @param User    $context
     * @param Content $content
     *
     * @return array
     */
    public function askingForPurchasingSponsorship(User $context, Content $content): array;
}
