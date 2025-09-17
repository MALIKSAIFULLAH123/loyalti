<?php

namespace MetaFox\Chat\Database\Importers;

use MetaFox\Chat\Models\Message as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class MessageImporter extends JsonImporter
{
    protected array $requiredColumns = ['room_id', 'user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$room',
            '$user',
        ]);

        $this->remapEmoji('message', true);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'               => $entry['$oid'],
            'room_id'          => $entry['room_id'],
            'total_attachment' => $entry['total_attachment'] ?? 0,
            'type'             => $entry['type'] ?? 'text',
            'user_id'          => $entry['user_id'],
            'user_type'        => $entry['user_type'],
            'message'          => html_entity_decode($entry['message'] ?? ''),
            'extra'            => $entry['extra'] ?? null,
            'reactions'        => $entry['reactions'] ?? null,
            'seen_users'       => $entry['seen_users'] ?? null,
            'created_at'       => $entry['created_at'] ?? now(),
            'updated_at'       => $entry['updated_at'] ?? $entry['created_at'] ?? now(),
        ]);
    }
}
