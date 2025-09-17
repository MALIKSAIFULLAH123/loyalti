<?php

namespace MetaFox\Group\Database\Importers;

use MetaFox\Group\Models\Request as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class RequestImporter extends JsonImporter
{
    protected array $requiredColumns = [
        'group_id',
        'user_id',
        'user_type',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs(['$group', '$user']);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'] ?? null,
            'group_id'   => $entry['group_id'] ?? null,
            'user_id'    => $entry['user_id'] ?? null,
            'user_type'  => $entry['user_type'] ?? null,
            'status_id'  => $this->handleStatus($entry['status_id'] ?? null),
            'created_at' => $entry['created_at'] ?? now(),
            'updated_at' => $entry['updated_at'] ?? null,
        ]);
    }

    private function handleStatus(?int $statusId): int
    {
        $statusList = [
            Model::STATUS_PENDING,
            Model::STATUS_APPROVED,
            Model::STATUS_DENIED,
        ];

        if ($statusId && in_array($statusId, $statusList)) {
            return $statusId;
        }

        return Model::STATUS_PENDING;
    }
}
