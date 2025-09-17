<?php

namespace MetaFox\ChatPlus\Database\Importers;

use MetaFox\ChatPlus\Models\Job as Model;
use MetaFox\Importer\Models\Entry;
use MetaFox\Importer\Supports\Emoji;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class JobImporter extends JsonImporter
{
    protected array $requiredColumns = [];

    private array $allUsers           = [];
    private array $emojiList          = [];
    private string $regexEmojiPattern = '';

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforeImport(): void
    {
        $this->emojiList         = Emoji::getAllEmoji();
        $this->regexEmojiPattern = $this->getRegexPattern($this->emojiList);
        $users                   = [];
        foreach ($this->entries as $entry) {
            $users = array_merge($users, $entry['$users'] ?? []);
        }
        $users          = array_unique($users);
        $this->allUsers = Entry::query()->whereIn('ref_id', $users)
            ->get(['ref_id', 'resource_id'])
            ->whereNotNull('resource_id')
            ->pluck('resource_id', 'ref_id')
            ->toArray();
    }

    public function processImport()
    {
        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        if (empty($entry['$users'])) {
            return;
        }
        $entry['users'] = array_map(function ($uid) {
            return $this->allUsers[$uid] ?? 0;
        }, $entry['$users']);
        [, $entry['conversation_id']] = explode('#', $entry['conversation_id']);
        unset($entry['$id']);
        unset($entry['$users']);
        foreach ($entry['messages'] as &$message) {
            $message['user_id'] = $this->allUsers[$message['$user']] ?? 0;
            $message['text']    = $this->parseEmoji($message['text']);
            unset($message['$user']);
        }
        $oid = $entry['$oid'];
        unset($entry['$oid']);
        $this->addEntryToBatch(Model::class, [
            'id'      => $oid,
            'is_sent' => 0,
            'name'    => 'onImportConversation',
            'data'    => json_encode($entry),
        ]);
    }

    public function parseEmoji(string $text): string
    {
        if (!$text) {
            return '';
        }

        return $this->handleEmoji($this->emojiList, $this->regexEmojiPattern, $text);
    }
}
