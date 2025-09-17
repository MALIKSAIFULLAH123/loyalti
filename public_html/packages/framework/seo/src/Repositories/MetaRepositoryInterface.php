<?php

namespace MetaFox\SEO\Repositories;

use MetaFox\SEO\Models\Meta;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Meta.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface MetaRepositoryInterface
{
    /**
     * @param  string $package
     * @param  array  $pages
     * @return void
     */
    public function setupSEOMetas(string $package, array $pages): void;

    public function dumpSEOMetas(string $package): array;

    public function createSampleMeta(string $name, string $url = null): Meta;

    public function getByName(string $name): ?Meta;

    public function getSeoSharingView(string $resolution, string $nameOrUrl, mixed $type = null, mixed $id = null, \Closure $callback = null);

    /**
     * @param  string        $resolution
     * @param  string        $nameOrUrl
     * @param  mixed|null    $type
     * @param  mixed|null    $id
     * @param  \Closure|null $callback
     * @return mixed
     */
    public function getSeoSharingData(string $resolution, string $nameOrUrl, mixed $type = null, mixed $id = null, \Closure $callback = null);
}
