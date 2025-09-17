<?php

namespace MetaFox\Page\Repositories;

use MetaFox\Platform\Contracts\User;

interface InfoSettingRepositoryInterface
{
    /**
     * @param  int   $pageId
     * @param  User  $context
     * @return array
     */
    public function getInfoSettings(User $context, int $pageId): array;

    /**
     * @param  User  $context
     * @param  int   $pageId
     * @return array
     */
    public function getAboutSettings(User $context, int $pageId): array;
}
