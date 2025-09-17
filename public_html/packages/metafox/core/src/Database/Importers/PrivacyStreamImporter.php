<?php

namespace MetaFox\Core\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Core\Models\PrivacyStream as Model;

class PrivacyStreamImporter extends JsonImporter
{
    protected array $modelClass = [];
    public function getModelClass(): string
    {
        return Model::class;
    }

    protected array $requiredColumns = ['item_id', 'item_type'];

    // batch to raw query in database.
    public function processImport()
    {
        $this->remapRefs([
            '$item' => ['item_id', 'item_type'],
            '$privacy',
        ]);

        $this->processImportEntries();
        if (count($this->modelClass)) {
            foreach ($this->modelClass as $model) {
                $this->upsertBatchEntriesInChunked($model, ['id']);
            }
        } else {
            $this->upsertBatchEntriesInChunked($this->getModelClass(), ['id']);
        }
    }

    public function processImportEntry(array &$entry): void
    {
        $oid        = $entry['$oid'];
        $modelClass = $entry['privacy_class'] ?? null;
        if ($modelClass) {
            if (!in_array($modelClass, $this->modelClass)) {
                $this->modelClass[] = $modelClass;
            }
            $this->addEntryToBatch($modelClass, [
                'id'         => $oid,
                'item_id'    => $entry['item_id'],
                'privacy_id' => $entry['privacy_id'] ?? $entry['default_privacy_id'],
            ]);
        } else {
            $this->addEntryToBatch($this->getModelClass(), [
                'id'         => $oid,
                'item_id'    => $entry['item_id'],
                'item_type'  => $entry['item_type'],
                'privacy_id' => $entry['privacy_id'] ?? $entry['default_privacy_id'],
            ]);
        }
    }
}
