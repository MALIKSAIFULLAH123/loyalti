<?php

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Activity\Models\PrivacyMember as ActivityPrivacyMember;
use MetaFox\Activity\Models\Subscription;
use MetaFox\Activity\Support\Support;
use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Friend\Models\TagFriend;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Importer\Models\Bundle;
use MetaFox\Importer\Models\Entry;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Importer\Supports\Emoji;
use MetaFox\Importer\Supports\JsonImport;
use MetaFox\Importer\Supports\Status;
use MetaFox\Localize\Models\Currency;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\BigNumberId;
use MetaFox\Platform\Contracts\UniqueIdInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\User\Models\UserPrivacy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * @property string[] $fillable
 */
class JsonImporter
{
    protected string $source;

    protected array $batchedData = [];

    protected array $entries = [];

    protected ?Bundle $bundle;

    /** @var array */
    protected array $requiredColumns = [];

    /**
     * @var array
     */
    protected array $uniqueColumns = [];

    protected bool $keepOldId = false;

    protected bool $priorityKeepOldIdSetting = false;

    protected array $mentionUsers = [];

    protected array $privacyMapping = [
        'phpfox' => [
            '0' => MetaFoxPrivacy::EVERYONE,
            '1' => MetaFoxPrivacy::FRIENDS,
            '2' => MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
            '3' => MetaFoxPrivacy::ONLY_ME,
            '4' => MetaFoxPrivacy::CUSTOM,
            '6' => MetaFoxPrivacy::MEMBERS,
        ],
    ];

    protected array $landingPageMapping = [
        'phpfox' => [
            'blog'        => 'blog',
            'event'       => 'event',
            'forum'       => 'forum',
            'marketplace' => 'marketplace',
            'photo'       => 'photo',
            'poll'        => 'poll',
            'quiz'        => 'quiz',
            'v'           => 'video',
            'home'        => 'home',
            'info'        => 'about',
            'members'     => 'member',
        ],
    ];

    /**
     * example: MetaFox\Blog\Models\Blog::class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return '';
    }

    public function setup(array $entries, mixed $bundle): void
    {
        $this->entries = $entries;
        $this->bundle  = $bundle;
    }

    protected function processImportEntry(array &$entry): void {}

    /** get next Id */
    protected function nextSequenceBigNumberIdForResource(string $resource)
    {
        return resolve(UniqueIdInterface::class)->getUniqueId($resource);
    }

    public function getRequiredColumns(): array
    {
        return [];
    }

    public function processImportEntries(): void
    {
        foreach ($this->entries as &$entry) {
            if (!$this->validateRequiredColumns($entry)) {
                continue;
            }
            if (!$this->validateEntry($entry)) {
                $this->warning('Invalid entry', $entry);
                continue;
            }
            $this->processImportEntry($entry);
        }
    }

    public function validateRequiredColumns(array &$entry): bool
    {
        $missingColumns = [];
        $valid          = true;
        foreach ($this->requiredColumns as $column) {
            if (!isset($entry[$column])) {
                $missingColumns[] = $column;
                $valid            = false;
            }
        }

        if (!$valid) {
            $this->warning(sprintf('Entry %s : Missing columns [%s]', $entry['$id'], implode(', ', $missingColumns)), $entry);
        }

        return $valid;
    }

    public function validateEntry(array &$entry): bool
    {
        return true;
    }

    public function remapRefs(array $map): void
    {
        if (empty($map)) {
            return;
        }

        foreach ($map as $refColumn => $ref) {
            if (is_int($refColumn)) {
                $refColumn = $ref;
                $alias     = substr($ref, 1);
                $ref       = ["{$alias}_id", "{$alias}_type"];
            }
            $this->remapRef($refColumn, $ref[0], $ref[1] ?? null);
        }
    }

    /**
     * @return void
     */
    public function insertEntries(): void
    {
        $index  = 0;
        $bundle = $this->bundle;

        // keep duplicate entry.
        $exists     = [];
        $insertData = array_map(function ($entry) use ($bundle, &$exists, &$index) {
            if (isset($exists[$entry['$id']])) { // prevent duplicate
                return false;
            }

            $exists[$entry['$id']] = true;

            return [
                'bundle_id'     => $bundle->id,
                'resource_id'   => $entry['$oid'] ?? null,
                'resource_type' => $bundle->resource,
                'source'        => $bundle->source,
                'ref_id'        => $entry['$id'],
                'status'        => Status::pending,
                'total_retry'   => 1,
                'priority'      => $bundle->priority,
                'filename'      => $bundle->filename,
                'entry_index'   => $index++,
            ];
        }, $this->entries);

        $insertData = array_filter($insertData, function ($row) {
            return (bool) $row;
        });

        Entry::withoutEvents(function () use ($insertData, $bundle) {
            Log::channel('importer')
                ->debug(sprintf('insertEntries %d %s', count($insertData), $bundle->resource));

            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                Entry::query()->upsert(
                    $chunk,
                    ['ref_id', 'source'],
                    ['resource_id', 'source', 'status', 'bundle_id', 'filename', 'priority']
                );
            }
        });
    }

    public function beforePrepare(): void {}

    public function beforeImport(): void {}

    public function afterImport(): void {}

    public function remapLandingPage(): void
    {
        $source = $this->bundle->source;

        foreach ($this->entries as &$entry) {
            $key                   = Arr::get($entry, 'landing_page', 'home');
            $landingPage           = Arr::get($this->landingPageMapping, "$source.$key", 'home');
            $entry['landing_page'] = $landingPage;
        }
    }

    protected function remapCurrency(): void
    {
        $this->remapRelation('$currency', 'currency_id', Currency::class, 'code', 'USD');
    }

    /**
     * Add item to batch.
     * @param string $modelClass
     * @param array  $item
     * @return void
     */
    protected function addEntryToBatch(string $modelClass, array $item): void
    {
        if (!array_key_exists($modelClass, $this->batchedData)) {
            $this->batchedData[$modelClass] = [];
        }

        $this->batchedData[$modelClass][] = $item;
    }

    protected function getBatchEntries($resource): array
    {
        if (!array_key_exists($resource, $this->batchedData)) {
            return [];
        }

        return $this->batchedData[$resource];
    }

    protected function statChunkEntries(string $resource)
    {
        $total = count($this->batchedData[$resource] ?? []);
        $this->debug(sprintf('statChunkEntries %s: %s entries', $resource, $total));
    }

    protected function getChunkEntries(string $resource, int $length)
    {
        if (!array_key_exists($resource, $this->batchedData)) {
            return [];
        }

        return array_chunk($this->batchedData[$resource], $length);
    }

    /**
     * get unique string id.
     */
    protected function getUniqueColumns(): array
    {
        return [];
    }

    protected function loadExistsIdFromUniqueColumns(): void
    {
        if (empty($this->uniqueColumns)) {
            return;
        }

        foreach ($this->uniqueColumns as $column) {
            $this->_loadMissingIds($this->bundle->resource, $column);
        }
    }

    protected function loadMissingBigNumberIds()
    {
        $resource = $this->bundle->resource;
        $model    = JsonImport::getModelForResource($resource);
        $sequence = $model instanceof BigNumberId;

        if (!$sequence) {
            return;
        }

        foreach ($this->entries as &$entry) {
            if ($entry['$oid'] ?? null) {
                continue;
            }
            $entry['$oid'] = $this->nextSequenceBigNumberIdForResource($resource);
        }
    }

    protected function loadPreservedIds()
    {
        $keepOldId        = $this->keepOldId;
        $settingKeepOldId = (bool) self::getSiteSettingValue('importer.importer_keep_item_id', false);

        if ($this->priorityKeepOldIdSetting) {
            $keepOldId = $settingKeepOldId;
        }

        if ($keepOldId) {
            foreach ($this->entries as &$entry) {
                [, $id] = explode('#', $entry['$id']);
                $entry['$oid'] = (int) $id;
            }

            return;
        }
        // count missing id.
        $total    = 0;
        $resource = $this->bundle->resource;
        foreach ($this->entries as &$entry) {
            if ($entry['$oid'] ?? null) {
                continue;
            }
            $total++;
        }
        if (!$total) {
            return;
        }
        $start = $this->nextIdForResource($resource, $total);
        foreach ($this->entries as &$entry) {
            if ($entry['$oid'] ?? null) {
                continue;
            }
            $entry['$oid'] = $start++;
        }
    }

    /**
     * Got string[] of ref column.
     * @code
     *      $importer->pluckRefOf($entries, '$id')
     * @endcode
     * @param string $column
     * @return array
     */
    protected function pickEntriesValue(string $column): array
    {
        return array_filter(array_map(function ($entry) use ($column) {
            return Arr::get($entry, $column);
        }, $this->entries), function ($item) {
            return $item !== null && $item !== '';
        });
    }

    /**
     * Got string[] of ref column.
     * @code
     *      $importer->pluckRefMap($entries, '$id')
     * @endcode
     * @param string $column
     * @return array
     */
    protected function pluckRefMap(string $column): array
    {
        $map = [];
        foreach ($this->entries as &$entry) {
            $map[$entry['$id']] = $entry[$column];
        }

        return $map;
    }

    /**
     * @code
     *      $this->remapRelation(Currency:class, '$currency','code', 'currency_code')
     * @endcode
     * @param string     $refColumn
     * @param string     $toColumn
     * @param string     $modelClass
     * @param string     $fromColumn
     * @param mixed|null $default
     * @return void
     */
    protected function remapRelation(
        string $refColumn,
        string $toColumn,
        string $modelClass,
        string $fromColumn,
        mixed  $default = null
    ): void
    {
        $tempColumn = '_' . $refColumn;
        $this->remapRef($refColumn, $tempColumn, null);
        $values = $this->pickEntriesValue($tempColumn);
        /** @var Model $model */
        $model       = resolve($modelClass);
        $keyName     = $model->getKeyName();
        $relationMap = DB::table($model->getTable())
            ->whereIn($keyName, $values)
            ->pluck($fromColumn, $keyName)
            ->toArray();

        foreach ($this->entries as &$entry) {
            $key = $entry[$tempColumn] ?? null;
            if (null == $key) {
                $entry[$toColumn] = $default;
            }

            $entry[$toColumn] = $relationMap[$key] ?? $default;
        }
    }

    private function remapRef(string $refColumn, ?string $idColunn, ?string $typeColumn): array
    {
        $map    = [];
        $values = $this->pickEntriesValue($refColumn);
        $rows   = Entry::query()->whereIn('ref_id', $values)
            ->get(['ref_id', 'resource_id', 'resource_type'])
            ->whereNotNull('resource_id')
            ->toArray();

        array_map(function ($row) use (&$map) {
            $map[$row['ref_id']] = [$row['resource_id'], $row['resource_type']];
        }, $rows);

        foreach ($this->entries as &$entry) {
            $key = Arr::get($entry, $refColumn);

            if (!$key) {
                continue;
            }

            $item = $map[$key] ?? null;

            if (!$item) {
                continue;
            }

            if ($idColunn) {
                $entry[$idColunn] = $item[0];
            }

            if ($typeColumn) {
                $entry[$typeColumn] = $item[1];
            }
        }

        return $map;
    }

    /**
     * call this method before run import().
     * @return void
     */
    public function fillIdFromEntries()
    {
        $source   = $this->bundle->source;
        $filename = $this->bundle->filename;
        $ref      = $this->pickEntriesValue('$id');

        $map = Entry::query()->where('source', $source)
            ->whereIn('ref_id', $ref)
            ->pluck('resource_id', 'ref_id')
            ->toArray();

        foreach ($this->entries as &$entry) {
            $key = $map[$entry['$id']] ?? null;
            if (!$key) {
                $this->error(sprintf('Failed getting id %s:%s', $filename, $entry['$id']));
            }
            $entry['$oid'] = $key;
        }
    }

    public function loadExistsIdFromImporterEntries()
    {
        $ref      = $this->pickEntriesValue('$id');
        $resource = $this->bundle->source;

        $map = Entry::query()->where('source', $resource)
            ->whereNotNull('resource_id')
            ->whereIn('ref_id', $ref)
            ->pluck('resource_id', 'ref_id')
            ->toArray();

        foreach ($this->entries as &$entry) {
            $key = $entry['$id'];
            if ($entry['$oid'] ?? null) {
                continue;
            }
            if (array_key_exists($key, $map)) {
                $entry['$oid'] = $map[$key];
            }
        }
    }

    protected function _loadMissingIds(string $resource, string $column)
    {
        $keys = $this->pickEntriesValue($column);

        $model = JsonImport::getMorphedModel($resource);

        /** @var Builder $query */
        $query = app()->call([$model, 'query']);
        $map   = $query->whereIn($column, $keys)->pluck('id', $column)->toArray();

        foreach ($this->entries as &$entry) {
            $key = $entry[$column] ?? null;
            if ($key && array_key_exists($key, $map)) {
                $entry['data_exist'] = true;
                $entry['$oid']       = $map[$key];
            }
        }
    }

    public function afterPrepare(): void {}

    public function processPrepare(): void
    {
        $this->loadExistsIdFromImporterEntries();
        $this->loadExistsIdFromUniqueColumns();
        $this->loadMissingBigNumberIds();
        $this->loadPreservedIds();
        $this->insertEntries();
    }

    public function nextIdForResource(string $resourceName, int $amount = 1): int
    {
        $idTable = 'importer_ids';

        $start = (int) DB::table('importer_ids')
            ->select('value')
            ->where('resource', $resourceName)
            ->value('value');

        if (!$start) {
            /** @var Model $modelClass */
            $modelClass = JsonImport::getModelForResource($resourceName);

            $table2 = $modelClass->getTable();

            $start = (int) DB::table($table2)->max($modelClass->getKeyName());

            $value = $start + $amount + 1;

            DB::table($idTable)->upsert([
                'resource' => $resourceName,
                'value'    => $value,
            ], ['resource']);

            $this->warning(sprintf('Init importer_ids( %s, %s )', $resourceName, $start));

            return $start + 1;
        }

        DB::table($idTable)->increment('value', $amount);

        return $start + 1;
    }

    public function info(string $message, array $extras = []): void
    {
        Log::channel('importer')->info($message, $extras);
    }

    public function error(string $message, array $extras = []): void
    {
        Log::channel('importer')->error($message, $extras);
    }

    public function warning(string $message, array $extras = []): void
    {
        Log::channel('importer')->warning($message, $extras);
    }

    public function debug(string $message, array $extras = []): void
    {
        Log::channel('importer')->debug($message, $extras);
    }

    public function array_unique_values(array $array, array $uniqueColumns): array
    {
        if (count($uniqueColumns) == 1) {
            $array = array_column($array, null, $uniqueColumns[0]);
        } else {
            $existed = [];
            foreach ($array as $key => $item) {
                $uniqueKey = implode('_', array_map(function ($x) use ($item) {
                    return (string) ($item[$x] ?? '');
                }, $uniqueColumns));
                if (isset($existed[$uniqueKey])) {
                    unset($array[$existed[$uniqueKey]]);
                } else {
                    $existed[$uniqueKey] = $key;
                }
            }
        }

        return array_values($array);
    }

    public function processImport()
    {
        throw new \RuntimeException(sprintf('Missed method %s::processImport()', get_class($this)));
    }

    public function upsertBatchEntriesInChunked(string $modelClass, array $uniqueKeys, int $limit = 100)
    {
        $this->statChunkEntries($modelClass);

        $modelClass::withoutEvents(function () use ($modelClass, $uniqueKeys, $limit) {
            $chunks = $this->getChunkEntries($modelClass, $limit);
            foreach ($chunks as $chunk) {
                try {
                    $chunk = $this->array_unique_values($chunk, $uniqueKeys);
                    $modelClass::query()->upsert($chunk, $uniqueKeys);
                } catch (\Exception $exception) {
                    $this->error(sprintf('%s:%s', __METHOD__, $exception->getMessage()));
                }
            }
        });
    }

    public function insertBatchEntriesInChunked(string $modelClass, int $limit = 100)
    {
        $this->statChunkEntries($modelClass);

        $modelClass::withoutEvents(function () use ($modelClass, $limit) {
            $chunks = $this->getChunkEntries($modelClass, $limit);
            foreach ($chunks as $chunk) {
                try {
                    $modelClass::query()->insert($chunk);
                } catch (\Exception $exception) {
                    $this->error($exception->getMessage());
                }
            }
        });
    }

    public function exportBundledEntries(array &$data, string $resource, int $priority = 0, string $suffix = ''): void
    {
        if (!count($data)) {
            return;
        }

        $index    = md5($this->bundle->filename . $resource . $suffix);
        $filename = 'storage/app/importer/data/temp/' . $resource . '/' . $index . '.json';
        $realpath = base_path($filename);

        if (!is_dir(dirname($realpath))) {
            mkdir(dirname($realpath), 0777, true);
        }

        file_put_contents($realpath, json_encode(array_values($data), JSON_PRETTY_PRINT));

        Bundle::query()->upsert([
            [
                'filename'    => $filename,
                'source'      => $this->bundle->source,
                'resource'    => $resource,
                'priority'    => $priority,
                'total_entry' => count($data),
                'status'      => Status::initial,
                'created_at'  => now(),
            ],
        ], ['filename'], ['source', 'resource', 'total_retry', 'status']);
    }

    public function exportBundled(string $resource, int $priority, ?string $column, \Closure $callback): void
    {
        $output = [];
        foreach ($this->entries as &$entry) {
            if (!$column || null === ($entry[$column] ?? null)) {
                continue;
            }
            $callback($output, $entry);
        }

        if (count($output)) {
            $this->info(sprintf('%s(%s, %s, %d)', __METHOD__, $resource, $column, count($output)));
            $this->exportBundledEntries($output, $resource, $priority, $column);
        }
    }

    public function appendFileBundle(string $column, int $priority = 2): void
    {
        $callback = function (array &$output, &$entry) use ($column) {
            $file = $entry[$column] ?? null;
            if (!is_array($file)) {
                return;
            }
            $originInfo = pathinfo($file['origin']);
            $output[]   = [
                '$id'           => $file['$id'],
                '$origin'       => $file['$id'],
                '$user'         => $file['$user'] ?? null,
                '$storage'      => $file['$storage'] ?? null,
                'file_size'     => $file['file_size'] ?? null,
                'original_name' => $file['original_name'] ?? $originInfo['basename'] ?? null,
                'mime_type'     => $file['mime_type'] ?? null,
                'extension'     => $file['extension'] ?? $originInfo['extension'] ?? null,
                'variant'       => $file['variant'] ?? 'origin',
                'path'          => $file['origin'],
                'is_photo'      => !empty($file['variants']),
                'is_origin'     => 1,
            ];

            foreach ($file['variants'] as $index => $variant) {
                $variantInfo = pathinfo($variant['path']);
                $output[]    = [
                    '$id'           => sprintf('%s%s', $file['$id'], $index),
                    '$origin'       => $file['$id'],
                    '$user'         => $file['$user'] ?? null,
                    '$storage'      => $variant['$storage'] ?? $file['$storage'] ?? null,
                    'file_size'     => $file['file_size'] ?? null,
                    'original_name' => $file['original_name'] ?? $variantInfo['basename'] ?? null,
                    'mime_type'     => $file['mime_type'] ?? null,
                    'extension'     => $file['extension'] ?? $variantInfo['extension'] ?? null,
                    'variant'       => $variant['variant'],
                    'path'          => $variant['path'],
                    'is_photo'      => true,
                    'is_origin'     => 0,
                ];
            }
        };

        $this->exportBundled(StorageFile::ENTITY_TYPE, $priority, $column, $callback);
    }

    public function appendPhotoBundle(
        string $column,
        int    $priority = 3,
        string $relatedType = null,
        string $relatedClass = null,
        string $owner = '$owner'
    ): void
    {
        $callback = function (array &$output, &$entry) use ($column, $relatedType, $relatedClass, $owner) {
            $file = $entry[$column] ?? null;
            if (!is_array($file)) {
                return;
            }
            $output[] = [
                '$id'           => $file['$id'] . '.photo',
                '$image'        => $file,
                '$owner'        => $entry[$owner] ?? null,
                '$user'         => $entry['$user'] ?? null,
                'privacy'       => $entry['privacy'] ?? 0,
                'related_type'  => $relatedType,
                'related_class' => $relatedClass,
                'created_at'    => $entry['created_at'] ?? null,
                'updated_at'    => $entry['created_at'] ?? null,
            ];
        };

        $this->exportBundled(Photo::ENTITY_TYPE, $priority, $column, $callback);
    }

    public function processPrivacyStream(
        string $modelClass,
        string $privacyColumn = 'privacy',
        string $privacyListColumn = 'privacy_list',
        string $ownerIdColumn = '$owner'
    )
    {
        try {
            $dataItem = [];
            $data     = [];
            foreach ($this->entries as &$entry) {
                $entry[$privacyColumn] = $privacy = $this->privacyMap(Arr::get($entry, $privacyColumn, 0));
                $ownerId               = Arr::get($entry, $ownerIdColumn);
                if (!$ownerId) {
                    continue;
                }

                if ($privacy == MetaFoxPrivacy::CUSTOM && empty($entry[$privacyListColumn])) {
                    $privacy = MetaFoxPrivacy::ONLY_ME;
                }

                if ($privacy == MetaFoxPrivacy::CUSTOM) {
                    foreach ($entry[$privacyListColumn] as $list) {
                        $data[]     = [
                            '$id'                => '_p.' . $entry['$id'] . '.' . $list . '.' . $privacy,
                            '$privacy'           => $list . '.' . MetaFoxPrivacy::CUSTOM,
                            '$item'              => $entry['$id'],
                            'privacy'            => $privacy,
                            'default_privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                        ];
                        $dataItem[] = [
                            '$id'                => 'p.' . $entry['$id'] . '.' . $list . '.' . $privacy,
                            '$privacy'           => $list . '.' . MetaFoxPrivacy::CUSTOM,
                            '$item'              => $entry['$id'],
                            'privacy'            => $privacy,
                            'default_privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                            'privacy_class'      => $modelClass,
                        ];
                    }
                } else {
                    $privacyOutput = $privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS ? MetaFoxPrivacy::FRIENDS : $privacy;
                    $output        = [
                        '$id'                => 'p.' . $entry['$id'] . '.' . $privacyOutput,
                        '$item'              => $entry['$id'],
                        'privacy'            => $privacyOutput,
                        '$privacy'           => $ownerId . '.' . $privacyOutput,
                        'default_privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                        'privacy_class'      => $modelClass,
                    ];
                    if ($privacy == MetaFoxPrivacy::EVERYONE) {
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID;
                        $output['$privacy']           = null;
                    }
                    if ($privacy == MetaFoxPrivacy::MEMBERS) {
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID;
                        $output['$privacy']           = null;
                    }
                    if ($privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS) {
                        $extraItem         = $output;
                        $tempOutput        = $output;
                        $tempOutput['$id'] = '_' . $tempOutput['$id'];
                        unset($tempOutput['privacy_class']);
                        $extra                        = $tempOutput;
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID;
                        $output['privacy']            = MetaFoxPrivacy::FRIENDS_OF_FRIENDS;
                        $output['$privacy']           = null;
                        $output['$id']                = 'p.' . $entry['$id'] . '.' . MetaFoxPrivacy::FRIENDS_OF_FRIENDS;
                        $dataItem[]                   = $output;
                        $dataItem[]                   = $extraItem;
                        $output['$id']                = '_' . $output['$id'];
                        unset($output['privacy_class']);
                        $data[] = $output;
                        $data[] = $extra;
                        continue;
                    }

                    $dataItem[]    = $output;
                    $output['$id'] = '_' . $output['$id'];
                    unset($output['privacy_class']);
                    $data[] = $output;
                }
            }
            if ($modelClass != PrivacyStream::class) {
                $this->exportBundledEntries($dataItem, PrivacyStream::ENTITY_TYPE, 3, $modelClass);
            }
            $this->exportBundledEntries($data, PrivacyStream::ENTITY_TYPE, 3);
        } catch (\Exception $e) {
            $this->error(sprintf('%s:%s', __METHOD__, $e->getMessage()));
        }
    }

    public function privacyMapEntry(array $entry, string $privacyColumn = 'privacy', string $privacyListColumn = 'privacy_list'): string
    {
        $privacy = $this->privacyMap($entry[$privacyColumn] ?? 0);

        if ($privacy == MetaFoxPrivacy::CUSTOM && empty($entry[$privacyListColumn])) {
            $privacy = MetaFoxPrivacy::ONLY_ME;
        }

        return $privacy;
    }

    public function privacyMap(int|string $privacy)
    {
        $source = $this->bundle->source;

        return Arr::get($this->privacyMapping, "$source.$privacy", $privacy);
    }

    public function transformPrivacyList(mixed $privacy, string $privacyType, string $userCol = '$user', string $ownerCol = '$owner', string $suffixId = ''): void
    {
        $data = [];
        foreach ($this->entries as $entry) {
            $data[] = [
                '$id'          => $entry['$id'] . '.' . $privacy . $suffixId,
                '$item'        => $entry['$id'],
                '$user'        => $entry[$userCol],
                '$owner'       => $entry[$ownerCol],
                'privacy'      => $privacy,
                'privacy_type' => $privacyType,
            ];
        }

        $this->exportBundledEntries($data, Privacy::ENTITY_TYPE, 3, $privacyType);
    }

    public function getPrivacyList($entry): array
    {
        return [];
    }

    public function transformPrivacyMember(array $privacyList = [], string $privacyBy = '$user', string $ownerCol = null, string $modelClass = PrivacyMember::class): void
    {
        $data             = [];
        $dataActivity     = [];
        $resetPrivacyList = !count($privacyList);
        $map              = [
            MetaFoxPrivacy::EVERYONE           => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
            MetaFoxPrivacy::MEMBERS            => MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID,
            MetaFoxPrivacy::FRIENDS_OF_FRIENDS => MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID,
        ];
        foreach ($this->entries as $entry) {
            if (!empty($entry['data_exist'])) {
                continue;
            }
            if ($resetPrivacyList) {
                $privacyList = $this->getPrivacyList($entry);
            }
            foreach ($privacyList as $privacy) {
                $data[]         = [
                    '$id'                => 'pm.' . $entry[$privacyBy] . '.' . $entry[$ownerCol ?? $privacyBy] . '.' . $privacy,
                    '$privacy'           => $entry[$privacyBy] . '.' . $privacy,
                    '$user'              => $entry[$ownerCol ?? $privacyBy],
                    'default_privacy_id' => $map[$privacy] ?? null,
                    'privacy_class'      => $modelClass,
                ];
                $dataActivity[] = [
                    '$id'                => 'apm.' . $entry[$privacyBy] . '.' . $entry[$ownerCol ?? $privacyBy] . '.' . $privacy,
                    '$privacy'           => $entry[$privacyBy] . '.' . $privacy,
                    '$user'              => $entry[$ownerCol ?? $privacyBy],
                    'default_privacy_id' => $map[$privacy] ?? null,
                    'privacy_class'      => $modelClass,
                ];
            }
        }

        $this->exportBundledEntries($data, PrivacyMember::ENTITY_TYPE, 3, PrivacyMember::ENTITY_TYPE . ($ownerCol ?? $privacyBy));
        $this->exportBundledEntries($dataActivity, ActivityPrivacyMember::ENTITY_TYPE, 3, ActivityPrivacyMember::ENTITY_TYPE . ($ownerCol ?? $privacyBy));
    }

    public function transformActivitySubscription($userCol = '$user', $ownerCol = '$owner', $subSuperAdmin = false)
    {
        $data        = [];
        $superAdmins = [];
        if ($subSuperAdmin) {
            $superAdmins = resolve(UserRepositoryInterface::class)->getAllSuperAdmin();
        }
        foreach ($this->entries as $entry) {
            if (empty($superAdmins)) {
                $data[] = [
                    '$id'          => 'as.' . $entry[$userCol] . '.' . $entry[$ownerCol],
                    '$user'        => $entry[$userCol],
                    '$owner'       => $entry[$ownerCol],
                    'is_active'    => true,
                    'special_type' => null,
                ];
                continue;
            }

            foreach ($superAdmins as $superAdmin) {
                $data[] = [
                    '$id'          => 'as.' . $entry[$userCol] . '.admin#' . $superAdmin->entityId(),
                    '$user'        => $entry[$userCol],
                    '$owner'       => null,
                    'owner_id'     => $superAdmin->entityId(),
                    'is_active'    => true,
                    'special_type' => Support::ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED,
                ];
            }
        }
        $this->exportBundledEntries($data, Subscription::ENTITY_TYPE, 3, Subscription::ENTITY_TYPE . $userCol . $ownerCol);
    }

    /**
     * @param string $text
     * @param bool   $isHtmlContent
     * @param bool   $parseMention
     * @return string
     */
    public function parseText(string $text, bool $isHtmlContent = true, bool $parseMention = false, ?array $entry = []): string
    {
        $text    = html_entity_decode($text);
        $regex   = ['/(<)(?!\w|\/\w)/', '/(?<!\w|\/|\s|"|\')>/', '/>(\\r\\t)</', '/>(\\r)</'];
        $replace = ['&lt;', '&gt;', '><', '><'];
        if (!$isHtmlContent) {
            $text = preg_replace($regex, $replace, $text);
        } else {
            if (!Str::startsWith($text, '<p>')) {
                // Insert paragraph tag
                $text = '[MetaFox_SP]' . $text . '[MetaFox_EP]';
            }
            $regex[]   = '/(?<!>)(<img[^>]*>)(?!<\/)/';
            $replace[] = '[MetaFox_EP]$1[MetaFox_SP]';
            $text      = preg_replace($regex, $replace, $text);
            // Remove empty tag
            $text = preg_replace('/(?<!^)\[MetaFox_SP\]\[MetaFox_EP\]/', '', $text);
            // Remove useless break line
            $text = preg_replace('/\[MetaFox_SP\]\R{1,2}/', '[MetaFox_SP]', $text);
            $text = str_replace(['[MetaFox_SP]', '[MetaFox_EP]'], ['<p>', '</p>'], $text);
        }
        if ($parseMention) {
            $text = $this->parseMention($text, $entry);
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parseMention(string $text, array $entry = []): string
    {
        $allMention  = [];
        $userMention = [];
        $text        = preg_replace_callback('/\[user=(\d+)\](.+?)\[\/user\]/u', function ($matches) use (&$allMention, &$userMention) {
            [, $userId, $name] = $matches;
            $key              = "mf_user_$userId";
            $allMention[$key] = "user#$userId";
            $userMention[]    = "user#$userId";

            return "[user=$key]" . $name . '[/user]';
        }, $text);
        $text        = preg_replace_callback('/\[page=(\d+)\](.+?)\[\/page\]/u', function ($matches) use (&$allMention) {
            [, $pageId, $name] = $matches;
            $key              = "mf_page_$pageId";
            $allMention[$key] = "page#$pageId";

            return "[page=$key]" . $name . '[/page]';
        }, $text);
        $text        = preg_replace_callback('/\[group=(\d+)\](.+?)\[\/group\]/u', function ($matches) use (&$allMention) {
            [, $groupId, $name] = $matches;
            $key              = "mf_group_$groupId";
            $allMention[$key] = "group#$groupId";

            return "[group=$key]" . $name . '[/group]';
        }, $text);
        if (!count($allMention)) {
            return $text;
        }
        $allMentionRefId = array_values($allMention);
        $rows            = Entry::query()->whereIn('ref_id', $allMentionRefId)
            ->get(['ref_id', 'resource_id', 'resource_type'])
            ->whereNotNull('resource_id')
            ->pluck('resource_id', 'ref_id')
            ->toArray();
        foreach ($allMention as &$refId) {
            $value = Arr::get($rows, $refId);
            if (!$value) {
                $refId = (int) str_replace(['user#', 'page#', 'group#'], ['', '', ''], $refId);
                continue;
            }
            $refId = $value;
        }
        $text = str_replace(array_keys($allMention), array_values($allMention), $text);
        if ($entry && count($userMention)) {
            foreach ($userMention as $owner) {
                $id = "tf.{$owner}_{$entry['$id']}";
                if (isset($this->mentionUsers[$id]) || !isset($entry['$id'])) {
                    continue;
                }
                $this->mentionUsers[$id] = [
                    '$id'        => $id,
                    '$user'      => $entry['$user'] ?? $entry['$owner'],
                    '$owner'     => $owner,
                    '$item'      => $entry['$id'],
                    'px'         => $entry['px'] ?? '0.000',
                    'py'         => $entry['py'] ?? '0.000',
                    'is_mention' => 1,
                    'content'    => $text,
                ];
            }
        }

        return $text;
    }

    public function processImportUserMention(): void
    {
        if (!count($this->mentionUsers)) {
            return;
        }
        $chunks = array_chunk(array_values($this->mentionUsers), 1000);
        foreach ($chunks as $key => $chunk) {
            $this->exportBundledEntries($chunk, TagFriend::ENTITY_TYPE, 3, $key);
        }
    }

    public function importTagData(string $model): void
    {
        $data = [];
        foreach ($this->entries as $entry) {
            $data = array_merge($data, $entry['tags'] ?? [], $entry['hashtags'] ?? []);
        }
        $data = array_unique($data);
        if (!count($data)) {
            return;
        }
        $result  = Tag::query()
            ->whereIn('text', $data)
            ->pluck('id', 'text')
            ->toArray();
        $batch   = [];
        $itemId  = [];
        $existed = [];
        foreach ($this->entries as $entry) {
            $allTags = array_unique(array_merge($entry['tags'] ?? [], $entry['hashtags'] ?? []));
            if (!count($allTags)) {
                continue;
            }
            foreach ($allTags as $tag) {
                $key = $result[$tag] ?? null;
                if (!$key) {
                    continue;
                }
                if (in_array("{$entry['$oid']}_$key", $existed)) {
                    continue;
                }
                $existed[] = "{$entry['$oid']}_$key";
                $batch[]   = [
                    'item_id' => $entry['$oid'],
                    'tag_id'  => $key,
                ];
                $itemId[]  = $entry['$oid'];
            }
        }
        $model::query()->whereIn('item_id', $itemId)->delete();
        $model::query()->insert($batch);
    }

    public function getEntryRepository(): EntryRepositoryInterface
    {
        return resolve(EntryRepositoryInterface::class);
    }

    public function transformUserPrivacy(): void
    {
        $data = [];
        foreach ($this->entries as $entry) {
            if (empty($entry['permissions'])) {
                continue;
            }
            foreach ($entry['permissions'] as $permission => $value) {
                if ((int) $value === 0) {
                    continue; // Don't need to import privacy = 0
                }
                $data[] = [
                    '$id'      => 'perm.' . $entry['$id'] . '#' . $permission,
                    '$privacy' => $entry['$id'] . '.' . $value,
                    'privacy'  => $value,
                    '$user'    => $entry['$id'],
                    'name'     => $permission,
                ];
            }
        }
        $this->exportBundledEntries($data, UserPrivacy::ENTITY_TYPE, 6);
    }

    public function remapEmoji(string|array $fields = ['text', 'text_parsed'], bool $isTwaEmoji = false): void
    {
        $list    = $isTwaEmoji ? Emoji::getMessageEmoji() : Emoji::getCommentEmoji();
        $pattern = $this->getRegexPattern($list);

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($this->entries as &$entry) {
            foreach ($fields as $field) {
                $entry[$field] = $this->handleEmoji($list, $pattern, $entry[$field] ?? '');
            }
        }
    }

    public function handleEmoji(array $list, string $pattern, string $text): string
    {
        return preg_replace_callback('/' . $pattern . '/', function ($match) use ($list) {
            if (isset($list[$match[0]])) {
                return json_decode('"' . $list[$match[0]] . '"');
            }

            return $match[0];
        }, $text);
    }

    protected function getRegexPattern(array $list): string
    {
        $str = implode('[REGEX_KEY]', array_keys($list));

        $str = str_replace(['(', ')', '*', '-', '/', '|'], ['\(', '\)', '\*', '\-', '\/', '\|'], $str);

        return str_replace('[REGEX_KEY]', '|', $str);
    }

    public static function getSiteSettingValue(string $name, string $default = null): ?string
    {
        /** @var SiteSetting $model */
        $model = SiteSetting::query()
            ->where('name', '=', $name)
            ->first();

        if (!$model) {
            return $default;
        }

        return $model->getValue();
    }

    public function setPriorityKeepOldIdSetting(bool $priorityKeepOldIdSetting): void
    {
        $this->priorityKeepOldIdSetting = $priorityKeepOldIdSetting;
    }
}
