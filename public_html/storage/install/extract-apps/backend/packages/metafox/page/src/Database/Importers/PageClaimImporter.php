<?php

namespace MetaFox\Page\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Page\Models\PageClaim as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PageClaimImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'page_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$page',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        $this->addEntryToBatch(
            Model::class,
            [
                'id'         => $oid,
                'page_id'    => $entry['page_id'] ?? 0,
                'status_id'  => $this->handleStatus($entry['status_id'] ?? null),
                'user_id'    => $entry['user_id'] ?? null,
                'user_type'  => $entry['user_type'] ?? null,
                'message'    => $entry['message'] ?? null,
                'updated_at' => $entry['updated_at'] ?? null,
                'created_at' => $entry['created_at'] ?? null,
            ]
        );
    }

    private function handleStatus(?int $statusId): int
    {
        $statusList = [0, 1];

        if ($statusId && in_array($statusId, $statusList)) {
            return $statusId;
        }

        return 0;
    }
}
