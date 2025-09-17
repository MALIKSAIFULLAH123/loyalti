<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Forum\Models\ForumPostQuote as Model;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumPostQuoteImporter extends JsonImporter
{
    protected array $requiredColumns = ['quote_content', 'post_id', 'quote_user_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$post'      => ['post_id'],
            '$quoteUser' => ['quote_user_id', 'quote_user_type'],
        ]);
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $this->addEntryToBatch(Model::class, [
            'id'              => $entry['$oid'] ?? null,
            'post_id'         => $entry['post_id'] ?? null,
            'quote_id'        => 0,
            'quote_content'   => $this->parseText($entry['quote_content']),
            'quote_user_id'   => $entry['quote_user_id'] ?? null,
            'quote_user_type' => $entry['quote_user_type'] ?? null,
            'created_at'      => $entry['created_at'] ?? now(),
            'updated_at'      => $entry['updated_at'] ?? null,
        ]);
    }
}
