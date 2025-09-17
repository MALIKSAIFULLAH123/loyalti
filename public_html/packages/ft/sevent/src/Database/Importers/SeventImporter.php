<?php

namespace Foxexpert\Sevent\Database\Importers;

use Foxexpert\Sevent\Models\SeventTagData;
use Foxexpert\Sevent\Models\SeventText;
use Foxexpert\Sevent\Models\PrivacyStream;
use MetaFox\Platform\Support\JsonImporter;
use Foxexpert\Sevent\Models\Sevent as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SeventImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$photo');
        $this->processPrivacyStream(PrivacyStream::class);
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user',
            '$photo.$id' => ['image_file_id'],
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(SeventText::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];
        $this->addEntryToBatch(
            Model::class,
            [
                'id'               => $oid,
                'title'            => html_entity_decode($entry['title'] ?? ''),
                'module_id'        => $entry['module_id'] ?? Model::ENTITY_TYPE,
                'privacy'          => $this->privacyMapEntry($entry),
                'is_draft'         => $entry['is_draft'] ?? 0,
                'is_featured'      => $entry['is_featured'] ?? 0,
                'is_sponsor'       => $entry['is_sponsor'] ?? 0,
                'is_approved'      => $entry['is_approved'] ?? 1,
                'tags'             => json_encode($entry['tags'] ?? []),
                'user_id'          => $entry['user_id'] ?? null,
                'user_type'        => $entry['user_type'] ?? null,
                'owner_id'         => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'       => $entry['owner_type'] ?? $entry['user_type'],
                'image_file_id'    => $entry['image_file_id'] ?? null,
                'updated_at'       => $entry['updated_at'] ?? null,
                'created_at'       => $entry['created_at'] ?? null,
                'total_like'       => $entry['total_like'] ?? 0,
                'total_share'      => $entry['total_share'] ?? 0,
                'total_comment'    => $entry['total_comment'] ?? 0,
                'total_reply'      => $entry['total_reply'] ?? 0,
                'total_attachment' => $entry['total_attachment'] ?? 0,
                'total_view'       => $entry['total_view'] ?? 0,
                'featured_at'      => $entry['featured_at'] ?? null,
                'sponsor_in_feed'  => $entry['sponsor_in_feed'] ?? 0,
            ]
        );

        $this->addEntryToBatch(
            SeventText::class,[
                'id'          => $oid,
                'text'        => $entry['text'] ?? '',
                'text_parsed' => $this->parseText($entry['text_parsed'] ?? ''),
            ]
        );
    }

    public function afterImport(): void
    {
        $this->importTagData(SeventTagData::class);
    }
}
