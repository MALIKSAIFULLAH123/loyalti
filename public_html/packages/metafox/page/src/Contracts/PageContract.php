<?php

namespace MetaFox\Page\Contracts;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;

interface PageContract
{
    /**
     * @param string $content
     * @return array
     */
    public function getMentions(string $content): array;

    /**
     * @param array $ids
     * @return Collection
     */
    public function getPagesForMention(array $ids): Collection;

    /**
     * @param User $user
     * @return Builder
     */
    public function getPageBuilder(User $user): Builder;

    /**
     * @return array
     */
    public function getListTypes(): array;

    /**
     * @param User $context
     * @param User $user
     * @return bool
     */
    public function isFollowing(User $context, User $user): bool;

    /**
     * @param Page $page
     * @return array
     */
    public function getProfileMenuSettings(Page $page): array;

    /**
     * @param Page $page
     * @return Builder
     */
    public function getMemberBuilderForLoginAsPage(Page $page): Builder;

    /**
     * @return array
     */
    public function getAllowApiRules(): array;

    /**
     * @param string $resolution
     * @return array
     */
    public function getInfoSettingsSupportByResolution(string $resolution): array;

    /**
     * @return bool
     */
    public function allowHtmlOnDescription(): bool;

    /**
     * @param Page $page
     * @return string
     */
    public function getCacheKeyDefaultTabActive(Page $page): string;

    /**
     * @param User $user
     * @param Page $page
     * @return string
     */
    public function getDefaultTabMenu(User $user, Page $page): string;
}
