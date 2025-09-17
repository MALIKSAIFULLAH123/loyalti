<?php

namespace MetaFox\Core\Repositories;

use MetaFox\App\Models\Package;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface AdminSearch.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface AdminSearchRepositoryInterface
{
    public function upsert(array $data): void;

    public function clean(): void;

    public function scanApp(Package $app): void;
}
