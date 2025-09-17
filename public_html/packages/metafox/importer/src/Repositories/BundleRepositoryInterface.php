<?php

namespace MetaFox\Importer\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Importer\Models\Bundle;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Bundle.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface BundleRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewBundles(array $attributes): Paginator;

    /**
     * Create schedule from json array.
     *
     * @param  string     $scheduleFilename
     * @param  array|null $filter
     * @return void
     */
    public function importScheduleJson(string $scheduleFilename, ?array $filter): void;

    /**
     * @param  string $archiveFileName
     * @param  string $chatType
     * @return void
     */
    public function importScheduleArchive(string $archiveFileName, string $chatType = 'chat', bool $keepItemId = false): void;

    /**
     * Pick next running.
     *
     * @return Bundle|null
     */
    public function pickStartBundle(): ?Bundle;

    /**
     * @return bool
     */
    public function isLocking(): bool;

    /**
     * @param  string $chatType
     * @return void
     */
    public function selectChatApp(string $chatType): void;

    /**
     * @param  bool $keepItemId
     * @return void
     */
    public function selectKeepItemId(bool $keepItemId): void;
}
