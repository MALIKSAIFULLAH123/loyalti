<?php

namespace MetaFox\Photo\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Importer\Models\Entry;
use MetaFox\Photo\Models\Photo as Model;
use MetaFox\Photo\Models\PhotoInfo;
use MetaFox\Photo\Models\PhotoPrivacyStream;
use MetaFox\Photo\Models\PhotoTagData;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class PhotoImporter extends JsonImporter
{
    protected bool $priorityKeepOldIdSetting = true;
    private array $relatedClass              = [];

    protected array $requiredColumns = [
        'user_id',
        'owner_id',
        'user_type',
        'owner_type',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$album' => ['album_id'],
            '$group',
            '$user',
            '$owner',
            '$image.$id' => ['image_file_id'],
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(PhotoInfo::class, ['id']);
        foreach ($this->relatedClass as $class => $field) {
            $this->upsertBatchEntriesInChunked($class, [$field]);
        }
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
        $this->processPrivacyStream(PhotoPrivacyStream::class);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'                 => $entry['$oid'],
            'title'              => html_entity_decode($entry['title'] ?? ''),
            'type_id'            => $entry['type_id'] ?? 0,
            'privacy'            => $this->privacyMapEntry($entry),
            'module_id'          => $entry['module_id'] ?? 'photo',
            'total_view'         => $entry['total_view'] ?? 0,
            'total_like'         => $entry['total_like'] ?? 0,
            'total_dislike'      => $entry['total_dislike'] ?? 0,
            'total_comment'      => $entry['total_comment'] ?? 0,
            'total_reply'        => $entry['total_reply'] ?? 0,
            'total_share'        => $entry['total_share'] ?? 0,
            'total_tag'          => $entry['total_tag'] ?? 0,
            'total_download'     => $entry['total_download'] ?? 0,
            'total_vote'         => $entry['total_vote'] ?? 0,
            'total_rating'       => $entry['total_rating'] ?? 0,
            'mature'             => $entry['mature'] ?? 0,
            'user_id'            => $entry['user_id'] ?? null,
            'user_type'          => $entry['user_type'] ?? null,
            'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
            'album_id'           => $entry['album_id'] ?? 0,
            'album_type'         => $entry['album_type'] ?? 0,
            'group_id'           => $entry['group_id'] ?? 0,
            'allow_rate'         => $entry['allow_rate'] ?? 1,
            'is_featured'        => $entry['is_featured'] ?? 0,
            'is_sponsor'         => $entry['is_sponsor'] ?? 0,
            'is_approved'        => $entry['is_approved'] ?? 1,
            'location_name'      => isset($entry['location_name']) ? html_entity_decode($entry['location_name']) : null,
            'location_latitude'  => $entry['location_latitude'] ?? null,
            'location_longitude' => $entry['location_longitude'] ?? null,
            'content'            => $entry['content'] ?? null,
            'is_temp'            => $entry['is_temp'] ?? 0,
            'ordering'           => $entry['ordering'] ?? 0,
            'sponsor_in_feed'    => $entry['sponsor_in_feed'] ?? 0,
            'updated_at'         => $entry['updated_at'] ?? now(),
            'created_at'         => $entry['created_at'] ?? null,
            'featured_at'        => $entry['featured_at'] ?? null,
            'deleted_at'         => $entry['deleted_at'] ?? null,
            'image_file_id'      => $entry['image_file_id'] ?? null,
        ]);

        $this->addEntryToBatch(
            PhotoInfo::class,
            [
                'id'          => $entry['$oid'],
                'text'        => $this->parseMention($entry['text'] ?? '', $entry),
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? '', false, true, $entry),
            ]
        );
        if (isset($entry['related_type'], $entry['related_class'], $entry['owner_id'])) {
            $this->addEntryToBatch($entry['related_class'], [
                $entry['related_type'] => $entry['$oid'],
                'id'                   => $entry['owner_id'], // TODO Improve unique column
            ]);
            $this->relatedClass[$entry['related_class']] = 'id';
        }
    }

    public function loadExistsIdFromImporterEntries()
    {
        $ref      = $this->pickEntriesValue('$id');
        $imageIds = $this->pickEntriesValue('$image.$id');
        $imageIds = array_map(function ($id) {
            return $id . '.photo';
        }, $imageIds);
        if (count($imageIds)) {
            $ref = array_merge($ref, $imageIds);
        }
        $resource = $this->bundle->source;

        $map = Entry::query()->where('source', $resource)
            ->whereNotNull('resource_id')
            ->whereIn('ref_id', $ref)
            ->pluck('resource_id', 'ref_id')
            ->toArray();

        foreach ($this->entries as &$entry) {
            $key = $entry['$id'];
            if (array_key_exists($key, $map)) {
                $entry['$oid'] = $map[$key];
                continue;
            }
            $secondKey = Arr::get($entry, '$image.$id', '') . '.photo';
            if (array_key_exists($secondKey, $map)) {
                $entry['$oid'] = $map[$secondKey];
            }
        }
    }

    public function beforePrepare(): void
    {
        if (!$this->bundle) {
            return;
        }
        if (!str_contains($this->bundle->filename, '/temp/photo/')) {
            return;
        }
        $this->setPriorityKeepOldIdSetting(false);
    }

    public function afterImport(): void
    {
        $this->processImportUserMention();
        $this->importTagData(PhotoTagData::class);
    }
}
