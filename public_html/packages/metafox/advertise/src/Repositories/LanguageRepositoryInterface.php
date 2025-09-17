<?php

namespace MetaFox\Advertise\Repositories;

use MetaFox\Platform\Contracts\Entity;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Language.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface LanguageRepositoryInterface
{
    /**
     * @param  Entity     $entity
     * @param  array|null $languages
     * @return void
     */
    public function addLanguages(Entity $entity, ?array $languages): void;

    /**
     * @param  Entity $entity
     * @return void
     */
    public function deleteLanguages(Entity $entity): void;
}
