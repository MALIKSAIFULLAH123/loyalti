<?php

namespace MetaFox\Importer\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MetaFox\Importer\Models\Bundle;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;
use MetaFox\Importer\Support\Browse\Scopes\Bundle\StatusScope;
use MetaFox\Importer\Supports\Status;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * resolve(MetaFox\Importer\Repositories\Eloquent\BundleRepository::class)->importScheduleJson('storage/app/importer/schedule.json');
 * Class BundleRepository.
 */
class BundleRepository extends AbstractRepository implements BundleRepositoryInterface
{
    public function model()
    {
        return Bundle::class;
    }

    public function viewBundles(array $attributes): Paginator
    {
        $query = $this->buildQueryViewBundle($attributes);

        return $query->orderBy('priority')->paginate($attributes['limit'] ?? 100);
    }

    private function buildQueryViewBundle(array $attributes)
    {
        $search = Arr::get($attributes, 'q');
        $status = Arr::get($attributes, 'status');

        $query = $this->getModel()->newModelQuery();

        if ($search) {
            $searchScope = new SearchScope($search, ['filename']);
            $query       = $query->addScope($searchScope);
        }

        if ($status) {
            $statusScope = new StatusScope();
            $statusScope->setStatus($status);

            $query = $query->addScope($statusScope);
        }

        return $query;
    }

    public function importScheduleArchive(string $archiveFileName, string $chatType = 'chat', bool $keepItemId = false): void
    {
        $disk = Storage::disk('local');
        $zip  = new \ZipArchive();
        $zip->open($archiveFileName, \ZipArchive::RDONLY);

        $schedule = $zip->getFromName('schedule.json');

        if (!$schedule) {
            throw new \InvalidArgumentException('Failed reading schedule.json, invalid zip archive format');
        }

        $jsonData = json_decode($schedule, true);

        if (!$jsonData) {
            throw new \InvalidArgumentException('Failed reading schedule.json, invalid schedule.json format');
        }

        if (!array_key_exists('source', $jsonData) || !array_key_exists('data', $jsonData)) {
            throw new \InvalidArgumentException('Failed reading schedule.json, invalid schedule.json format');
        }

        $basePath = 'importer';
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $filename = $zip->getNameIndex($index);
            $target   = implode(DIRECTORY_SEPARATOR, [$basePath, $filename]);

            if (Str::endsWith($filename, DIRECTORY_SEPARATOR)) {
                continue;
            }

            $disk->put($target, $zip->getStream($filename));

            Log::channel('importer')->info(sprintf('Extracting %s to %s', $filename, $target));
        }

        $jsonFile = $basePath . DIRECTORY_SEPARATOR . 'schedule.json';

        $jsonFile = $disk->path($jsonFile);

        $jsonFile = substr($jsonFile, strlen(base_path()) + 1);

        $this->importScheduleJson($jsonFile);
        $this->selectChatApp($chatType);
        $this->selectKeepItemId($keepItemId);
        $this->addLockFile();
    }

    /**
     * @param string     $scheduleFilename
     * @param array|null $filter
     */
    public function importScheduleJson(string $scheduleFilename, ?array $filter = []): void
    {
        $jsonData = json_decode(mf_get_contents(base_path($scheduleFilename)), true);
        $basePath = dirname($scheduleFilename);
        $source   = $jsonData['source'];

        foreach ($jsonData['data'] as $folderIndex => $data) {
            if ($filter && !in_array($data['resource'], $filter)) {
                continue;
            }
            $this->processData($basePath, $data, $folderIndex, $source);
        }
    }

    public function processData(
        string $basePath,
        array $data,
        int $folderIndex,
        string $source,
    ) {
        $dirname    = $data['path'];
        $insertData = [];
        $folder     = base_path(implode(DIRECTORY_SEPARATOR, [$basePath, $dirname]));

        if (!app('files')->exists($folder)) {
            Log::channel('importer')->info(sprintf('Folder doesn\'t exists: %s', $folder));

            return;
        }

        $files = app('files')->files($folder);
        // check file name in correct priority.

        foreach ($files as $file) {
            $filename   = substr($file->getPathname(), strlen(base_path()) + 1);
            $fileData   = json_decode(mf_get_contents(base_path($filename)), true);
            $totalEntry = is_countable($fileData) ? count($fileData) : 0;

            // how to order by indexing.
            $insertData[] = [
                'filename'    => $filename,
                'source'      => $source,
                'resource'    => $data['resource'],
                'priority'    => $data['priority'],
                'total_entry' => $totalEntry,
                'total_retry' => 0,
                'status'      => Status::initial,
                'created_at'  => now(),
                'folderIndex' => $folderIndex,
                'fileIndex'   => (int) ($file->getBasename('.json')),
            ];
        }
        $this->insertData($insertData);
    }

    public function writeFileEntries(string $basePath, array &$data, int &$index): void
    {
        if (empty($data)) {
            return;
        }
        $index++;
        $filename = base_path($basePath . '/data/tmpfiles/' . $index . '.json');

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }

    public function pickStartBundle(): ?Bundle
    {
        /** @var ?Bundle $bundle */
        $bundle = $this->getModel()
            ->newQuery()
            ->where('status', '=', Status::initial)
            ->orderBy('priority')
            ->orderBy('id')
            ->first();

        if (!$bundle) {
            /** @var ?Bundle $bundle */
            $bundle = $this->getModel()
                ->newQuery()
                ->where('status', '=', Status::processing)
                ->orderBy('priority')
                ->orderBy('id')
                ->first();
        }

        if (!$bundle) {
            $bundle = $this->getModel()
                ->newQuery()
                ->where('status', '=', Status::pending)
                ->orderBy('priority')
                ->orderBy('id')
                ->first();
        }

        return $bundle;
    }

    /**
     * @param array $insertData
     */
    public function insertData(array &$insertData): void
    {
        // ordering.
        uasort($insertData, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            if ($a['folderIndex'] !== $b['folderIndex']) {
                return $a['folderIndex'] - $b['folderIndex'];
            }

            return $a['fileIndex'] - $b['fileIndex'];
        });

        // cleanup data
        foreach ($insertData as $index => $row) {
            unset($insertData[$index]['folderIndex'], $insertData[$index]['fileIndex']);
        }

        // prepare chunk to insert
        $chunks = array_chunk($insertData, 500);

        foreach ($chunks as $chunk) {
            Bundle::query()->upsert($chunk, ['filename'], ['priority']);
        }
    }

    public function addLockFile()
    {
        $lockFile = storage_path('framework/importing.lock');
        if (!file_exists($lockFile)) {
            file_put_contents($lockFile, 'importing');
        }
    }

    public function deleteLockFile()
    {
        $lockFile = storage_path('framework/importing.lock');
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
    }

    public function isLocking(): bool
    {
        $lockFile = storage_path('framework/importing.lock');

        return file_exists($lockFile);
    }

    public function selectChatApp(string $chatType): void
    {
        if (!in_array($chatType, ['chat', 'chatplus'])) {
            return;
        }
        Settings::createSetting('importer', 'importer.importer_selected_chat_app', null, null, $chatType, 'string', false, true);
        localCacheStore()->clear();
        Settings::refresh();
    }

    public function selectKeepItemId(bool $keepItemId): void
    {
        Settings::createSetting('importer', 'importer.importer_keep_item_id', null, null, $keepItemId, 'boolean', false, true);
        localCacheStore()->clear();
        Settings::refresh();
    }
}
