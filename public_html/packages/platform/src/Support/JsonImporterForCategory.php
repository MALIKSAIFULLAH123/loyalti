<?php

namespace MetaFox\Platform\Support;

class JsonImporterForCategory extends JsonImporter
{
    public function getModelClass(): string
    {
        return '';
    }

    public function beforePrepare(): void
    {
        $this->getModelClass()::truncate();
    }

    public function processImport()
    {
        $this->remapRefs(['$parent']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch($this->getModelClass(), [
            'id'         => $entry['$oid'],
            'parent_id'  => $entry['parent_id'] ?? null,
            'name'       => html_entity_decode($entry['name'] ?? ''),
            'name_url'   => $entry['name_url'] ?? null,
            'ordering'   => $entry['ordering'] ?? 0,
            'total_item' => $entry['total_item'] ?? 0,
            'is_active'  => $entry['is_active'] ?? 1,
            'level'      => $entry['level'] ?? (!empty($entry['parent_id']) ? 2 : 1),
            'updated_at' => $entry['updated_at'] ?? null,
            'created_at' => $entry['created_at'] ?? null,
        ]);
    }
}
