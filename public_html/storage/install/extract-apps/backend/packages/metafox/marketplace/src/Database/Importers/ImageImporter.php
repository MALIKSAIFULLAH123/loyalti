<?php

namespace MetaFox\Marketplace\Database\Importers;

use MetaFox\Marketplace\Models\Image as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ImageImporter extends JsonImporter
{
    protected array $requiredColumns = ['listing_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendFileBundle('$image');
    }

    public function processImport()
    {
        $this->remapRefs(['$listing', '$image.$id' => ['image_file_id']]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'ordering'      => $entry['ordering'] ?? 1,
            'listing_id'    => $entry['listing_id'],
            'image_file_id' => $entry['image_file_id'] ?? null,
        ]);
    }
}
