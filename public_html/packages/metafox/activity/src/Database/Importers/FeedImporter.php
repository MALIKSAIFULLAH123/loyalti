<?php

namespace MetaFox\Activity\Database\Importers;

use Illuminate\Support\Arr;
use MetaFox\Activity\Models\ActivityTagData;
use MetaFox\Activity\Models\Feed as Model;
use MetaFox\Activity\Models\Stream;
use MetaFox\Activity\Models\Type;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Activity\Support\Support;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class FeedImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id', 'owner_id', 'item_id'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->processTime();
        $this->processActivityStream();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$owner', '$user', '$item', '$parentFeed',
        ]);

        $this->processImportEntries();

        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    public function processImportEntry(array &$entry): void
    {
        $oid = $entry['$oid'];

        if (!$this->checkFeedType($entry['type_id'])) {
            return;
        }

        $this->addEntryToBatch(
            Model::class,
            [
                'id'                 => $oid,
                'privacy'            => $this->privacyMapEntry($entry),
                'is_sponsor'         => $entry['is_sponsor'] ?? 0,
                'total_like'         => $entry['total_like'] ?? 0,
                'total_comment'      => $entry['total_comment'] ?? 0,
                'total_reply'        => $entry['total_reply'] ?? 0,
                'total_share'        => $entry['total_share'] ?? 0,
                'total_view'         => $entry['total_view'] ?? 0,
                'user_id'            => $entry['user_id'] ?? null,
                'user_type'          => $entry['user_type'] ?? null,
                'owner_id'           => $entry['owner_id'] ?? $entry['user_id'],
                'owner_type'         => $entry['owner_type'] ?? $entry['user_type'],
                'item_id'            => $entry['item_id'] ?? null,
                'item_type'          => $entry['item_type'] ?? null,
                'type_id'            => $entry['type_id'] ?? null,
                'status'             => $this->handleStatus($entry['status'] ?? null),
                'content'            => isset($entry['content']) ? $this->parseText($entry['content'], false, true, $entry) : null,
                'feed_reference'     => $entry['feed_reference'] ?? 0,
                'parent_feed_id'     => $entry['parentFeed_id'] ?? 0,
                'parent_module_id'   => $entry['parentModule_id'] ?? null,
                'created_at'         => $entry['created_at'] ?? null,
                'updated_at'         => $entry['updated_at'] ?? null,
                'latest_activity_at' => $entry['updated_at'] ?? null,
            ]
        );
    }

    public function afterImport(): void
    {
        $this->processImportUserMention();
        $this->importTagData(ActivityTagData::class);
    }

    private function processTime(): void
    {
        foreach ($this->entries as &$entry) {
            $createAt = $entry['created_at'] ?? now();

            $entry['created_at'] = $createAt;
            $entry['updated_at'] = $entry['updated_at'] ?? $createAt;
        }
    }

    private function checkFeedType(?string $type): bool
    {
        $feedType = resolve(TypeRepositoryInterface::class)->getTypeByType($type);

        return $feedType instanceof Type;
    }

    private function handleStatus(?string $status): string
    {
        $statusList = Support::getItemStatuses();

        if ($status && in_array($status, $statusList)) {
            return $status;
        }

        return MetaFoxConstant::ITEM_STATUS_APPROVED;
    }

    protected function processActivityStream(
        string $privacyColumn = 'privacy',
        string $privacyListColumn = 'privacy_list',
        string $ownerIdColumn = '$owner'
    ) {
        try {
            $dataItem = [];
            foreach ($this->entries as &$entry) {
                $entry[$privacyColumn] = $privacy = $this->privacyMap(Arr::get($entry, $privacyColumn, 0));
                $ownerId               = Arr::get($entry, $ownerIdColumn);

                if (!$ownerId) {
                    continue;
                }

                if ($privacy == MetaFoxPrivacy::CUSTOM && empty($entry[$privacyListColumn])) {
                    $privacy = MetaFoxPrivacy::ONLY_ME;
                }

                if ($privacy == MetaFoxPrivacy::CUSTOM) {
                    $streamItem = null;

                    foreach ($entry[$privacyListColumn] as $list) {
                        $dataItem[] = $streamItem = [
                            '$id'                => 'p.' . $entry['$id'] . '.' . $list . '.' . $privacy,
                            '$privacy'           => $list . '.' . MetaFoxPrivacy::CUSTOM,
                            'feed_id'            => $entry['$oid'],
                            '$owner'             => $entry['$owner'],
                            '$user'              => $entry['$user'],
                            '$item'              => $entry['$item'],
                            'privacy'            => $privacy,
                            'created_at'         => $entry['created_at'],
                            'updated_at'         => $entry['updated_at'],
                            'default_privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                        ];
                    }

                    $this->handleTagFriendStream($entry, $dataItem, $streamItem);
                } else {
                    $privacyOutput = $privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS ? MetaFoxPrivacy::FRIENDS : $privacy;
                    $output        = [
                        '$id'                => 'p.' . $entry['$id'] . '.' . $privacyOutput,
                        '$item'              => $entry['$item'],
                        'feed_id'            => $entry['$oid'],
                        '$owner'             => $entry['$owner'],
                        '$user'              => $entry['$user'],
                        'privacy'            => $privacyOutput,
                        '$privacy'           => $ownerId . '.' . $privacyOutput,
                        'created_at'         => $entry['created_at'],
                        'updated_at'         => $entry['updated_at'],
                        'default_privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                    ];
                    if ($privacy == MetaFoxPrivacy::EVERYONE) {
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID;
                        $output['$privacy']           = null;
                    }
                    if ($privacy == MetaFoxPrivacy::MEMBERS) {
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID;
                        $output['$privacy']           = null;
                    }
                    if ($privacy == MetaFoxPrivacy::FRIENDS_OF_FRIENDS) {
                        $extraItem                    = $output;
                        $output['default_privacy_id'] = MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID;
                        $output['privacy']            = MetaFoxPrivacy::FRIENDS_OF_FRIENDS;
                        $output['$privacy']           = null;
                        $output['$id']                = 'p.' . $entry['$id'] . '.' . MetaFoxPrivacy::FRIENDS_OF_FRIENDS;

                        $dataItem[] = $output;
                        $this->handleTagFriendStream($entry, $dataItem, $output);
                        $dataItem[] = $extraItem;
                        $this->handleTagFriendStream($entry, $dataItem, $extraItem);
                        continue;
                    }

                    $dataItem[] = $output;
                    $this->handleTagFriendStream($entry, $dataItem, $output);
                }
            }
            $this->exportBundledEntries($dataItem, Stream::ENTITY_TYPE, 3);
        } catch (\Exception $e) {
            $this->error(sprintf('%s:%s', __METHOD__, $e->getMessage()));
        }
    }

    private function handleTagFriendStream(array $entry, array &$dataItem, array $item): void
    {
        $tagFriends = Arr::get($entry, 'tag_friends') ?? [];
        if (empty($tagFriends)) {
            return;
        }

        foreach ($tagFriends as $tagFriend) {
            $newItem           = $item;
            $newItem['$id']    = 'tag.' . $newItem['$id'] . '.' . $tagFriend;
            $newItem['$owner'] = $tagFriend;

            $dataItem[] = $newItem;
        }
    }
}
