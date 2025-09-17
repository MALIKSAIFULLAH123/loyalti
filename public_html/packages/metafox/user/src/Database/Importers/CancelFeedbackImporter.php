<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\CancelFeedback as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class CancelFeedbackImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'user_group_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user'      => ['user_id', 'user_type'],
            '$userGroup' => ['user_group_id'],
        ]);

        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        // check duplicated by user_name or email or vise versa.
        $oid = $entry['$oid'];

        $this->addEntryToBatch(Model::class, [
            'id'            => $oid,
            'email'         => $entry['email'] ?? '',
            'name'          => $entry['name'] ?? '',
            'user_group_id' => $entry['user_group_id'] ?? 0,
            'feedback_text' => $entry['feedback_text'] ?? '',
            'reason_id'     => $entry['reason_id'] ?? 0,
            'reasons_given' => $entry['reasons_given'] ?? null,
            'user_id'       => $entry['user_id'] ?? 0,
            'user_type'     => $entry['user_type'] ?? 'user',
            'created_at'    => $entry['created_at'] ?? now(),
            'updated_at'    => $entry['updated_at'] ?? now(),
        ]);
    }
}
