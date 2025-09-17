<?php

namespace MetaFox\Forum\Contracts;

use MetaFox\Forum\Models\ForumPost;
use MetaFox\Platform\Contracts\User;

interface ForumPostSupportContract
{
    /**
     * @param  User      $context
     * @param  ForumPost $model
     * @return array
     */
    public function getCustomExtra(User $context, ForumPost $model): array;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deletePost(User $context, int $id): bool;

    /**
     * @return array
     */
    public function getRelations(): array;
}
