<?php

namespace MetaFox\Event\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Event\Models\Invite as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class InviteImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user', '$event',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'user_id'       => $entry['user_id'] ?? null,
            'user_type'     => $entry['user_type'] ?? null,
            'owner_id'      => $entry['owner_id'] ?? $entry['user_id'],
            'owner_type'    => $entry['owner_type'] ?? $entry['user_type'],
            'event_id'      => $entry['event_id'],
            'status_id'     => $this->handleStatus($entry['status_id'] ?? null),
            'invited_email' => $entry['invited_email'] ?? null,
            'updated_at'    => $entry['updated_at'] ?? null,
            'created_at'    => $entry['created_at'] ?? null,
        ]);
    }

    private function handleStatus(?int $statusId): int
    {
        $statusList = [
            Model::STATUS_PENDING, Model::STATUS_APPROVED,
            Model::STATUS_NOT_INVITE_AGAIN, Model::STATUS_DECLINED,
        ];

        if ($statusId && in_array($statusId, $statusList)) {
            return $statusId;
        }

        return Model::STATUS_PENDING;
    }
}
