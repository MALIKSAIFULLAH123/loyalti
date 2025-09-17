<?php

namespace MetaFox\Forum\Contracts;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

interface ForumThreadSupportContract
{
    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return array
     */
    public function getCustomPolicies(User $user, Content $resource): array;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canDisplayOnWiki(User $user): bool;

    /**
     * @return array
     */
    public function getRelations(): array;

    /**
     * @return array|null
     */
    public function getIntegratedItem(User $user, User $owner, ?Entity $entity = null, string $resolution = 'web'): ?array;

    /**
     * @return int
     */
    public function getDefaultMinimumTitleLength(): int;

    /**
     * @return int
     */
    public function getDefaultMaximumTitleLength(): int;

    /**
     * @param int $id
     *
     * @return ForumThread|null
     */
    public function getThread(int $id): ?ForumThread;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function checkCreateThreads(User $user): bool;
}
