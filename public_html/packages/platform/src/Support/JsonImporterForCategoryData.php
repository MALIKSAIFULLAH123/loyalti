<?php

namespace MetaFox\Platform\Support;

class JsonImporterForCategoryData extends JsonImporter
{
    protected array $requiredColumns = [
        'item_id',
        'category_id',
    ];
    public function getModelClass(): string
    {
        return '';
    }

    public function processImport()
    {
        $this->remapRefs(['$item', '$category']);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id'], 500);
    }

    public function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch($this->getModelClass(), [
            'id'          => $entry['$oid'],
            'item_id'     => $entry['item_id'],
            'category_id' => $entry['category_id'],
        ]);
    }
}
