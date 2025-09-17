<?php

namespace MetaFox\Chat\Database\Importers;

use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Attachment;
use MetaFox\Chat\Models\Message;
use MetaFox\Chat\Models\Room as Model;
use MetaFox\Chat\Models\Subscription;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class RoomImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id', 'anotherUser_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function beforeImport(): void
    {
        foreach ($this->entries as &$entry) {
            if (!isset($entry['$users']) || !is_array($entry['$users']) || count($entry['$users']) != 2) {
                continue;
            }
            $entry['$user']        = $entry['$users'][0];
            $entry['$anotherUser'] = $entry['$users'][1];
            $entry['$owner']       = $entry['$users'][0];
        }
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$owner',
            '$anotherUser',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $members = [$entry['user_id'], $entry['anotherUser_id']];
        sort($members);
        $this->addEntryToBatch(Model::class, [
            'id'          => $entry['$oid'],
            'uid'         => md5(json_encode($members)),
            'name'        => $entry['conversation_name'] ?? '',
            'user_id'     => $entry['user_id'],
            'user_type'   => $entry['user_type'],
            'owner_id'    => $entry['owner_id'],
            'owner_type'  => $entry['owner_type'],
            'is_archived' => (int) ($entry['is_archived'] ?? 0),
            'is_readonly' => (int) ($entry['is_readonly'] ?? 0),
            'type'        => 'd',
            'created_at'  => $entry['created_at'] ?? Carbon::createFromTimestamp($entry['time_stamp'])->toIso8601String(),
        ]);
    }

    public function afterPrepare(): void
    {
        $this->appendSubscriptionsBundle();
        $this->appendMessagesBundle();
    }

    public function appendSubscriptionsBundle()
    {
        $column   = '$users';
        $callback = function (array &$output, &$entry) use ($column) {
            $users = $entry[$column] ?? null;
            if (!is_array($users) || count($users) != 2) {
                return;
            }
            $output[] = [
                '$id'          => 'chat_subscription_' . $users[0] . '_' . $entry['$id'],
                '$room'        => $entry['$id'],
                '$user'        => $users[0],
                '$anotherUser' => $users[1],
                'room_name'    => $entry['conversation_name'] ?? '',
                'total_unseen' => $entry['unread'][$users[0]] ?? 0,
                'created_at'   => Carbon::createFromTimestamp($entry['time_stamp'] ?? now())->toIso8601String(),
            ];
            $output[] = [
                '$id'          => 'chat_subscription_' . $users[1] . '_' . $entry['$id'],
                '$room'        => $entry['$id'],
                '$user'        => $users[1],
                '$anotherUser' => $users[0],
                'room_name'    => $entry['conversation_name'] ?? '',
                'total_unseen' => $entry['unread'][$users[1]] ?? 0,
                'created_at'   => Carbon::createFromTimestamp($entry['time_stamp'] ?? now())->toIso8601String(),
            ];
        };

        $this->exportBundled(Subscription::ENTITY_TYPE, 13, $column, $callback);
    }

    public function appendMessagesBundle()
    {
        $column   = 'messages';
        $callback = function (array &$output, &$entry) use ($column) {
            $messages = $entry[$column] ?? null;
            if (!is_array($messages) || !count($messages)) {
                return;
            }
            foreach ($messages as $message) {
                $output[] = array_merge($message, [
                    '$id'        => 'mail#' . $message['message_id'],
                    '$room'      => $entry['$id'],
                    'message'    => $message['text'],
                    'created_at' => Carbon::createFromTimestamp($message['time_stamp'] ?? now())->toIso8601String(),
                ]);
            }
        };

        $callbackAttachment = function (array &$output, &$entry) use ($column) {
            $messages = $entry[$column] ?? null;
            if (!is_array($messages) || !count($messages)) {
                return;
            }
            foreach ($messages as $message) {
                if (empty($message['export_attachment']) || empty($message['attachments'])) {
                    continue;
                }
                foreach ($message['attachments'] as $attachment) {
                    if (empty($attachment['$id'])) {
                        continue;
                    }
                    $output[] = [
                        '$id'        => $attachment['$id'],
                        '$item'      => $attachment['$item'] ?? null,
                        '$user'      => $attachment['$user'] ?? $message['$user'],
                        '$file'      => $attachment['$file'] ?? null,
                        'created_at' => $attachment['created_at'] ?? null,
                    ];
                }
            }
        };
        $this->exportBundled(Message::ENTITY_TYPE, 13, $column, $callback);

        $this->exportBundled(Attachment::ENTITY_TYPE, 14, $column, $callbackAttachment);
    }
}
