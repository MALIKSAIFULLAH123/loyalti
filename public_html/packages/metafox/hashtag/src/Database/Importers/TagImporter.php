<?php

namespace MetaFox\Hashtag\Database\Importers;

use Illuminate\Support\Str;
use MetaFox\Hashtag\Models\Tag as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class TagImporter extends JsonImporter
{
    protected array $requiredColumns = ['text', 'tag_url'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'      => $entry['$oid'],
            'text'    => $entry['text'],
            'tag_url' => Str::slug($entry['tag_url']),
            'total_item' => $entry['total_item'] ?? 0
        ]);
    }
}
