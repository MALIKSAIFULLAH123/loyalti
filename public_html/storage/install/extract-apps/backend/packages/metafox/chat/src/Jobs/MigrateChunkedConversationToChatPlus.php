<?php

namespace MetaFox\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Models\Room;

class MigrateChunkedConversationToChatPlus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $roomIds = [])
    {
    }

    public function handle()
    {
        $rooms = Room::query()
            ->whereIn('id', $this->roomIds)
            ->with(['messages', 'subscriptions'])
            ->get();

        if (!$rooms->count()) {
            return;
        }

        foreach ($rooms as $room) {
            if (!$room instanceof Room) {
                continue;
            }

            $messages = $room->messages;

            $messagesData = [];
            foreach ($messages as $message) {
                $attachmentData = [];
                $attachments    = $message?->attachments;

                if (count($attachments->toArray())) {
                    foreach ($attachments as $attachment) {
                        $attachmentData[] = [
                            'attachment_id' => $attachment->id,
                            'time_stamp'    => $attachment->updated_at->timestamp,
                            'file_name'     => $attachment->file->original_name,
                            'file_size'     => $attachment->file->file_size,
                            'download_url'  => $attachment->file->url,
                            'is_image'      => $attachment->file->is_image,
                        ];
                    }
                }

                $messagesData[] = [
                    'message_id'       => $message->room_id . ':' . $message->id,
                    'user_id'          => $message->user_id ?? 0,
                    'text'             => $message->message,
                    'time_stamp'       => $message->updated_at->timestamp,
                    'total_attachment' => $message->total_attachment,
                    'attachments'      => $attachmentData,
                    'is_show'          => 1,
                    'is_deleted'       => 0,
                ];
            }

            if (count($messagesData) == 0) {
                continue;
            }

            $userIds = $room->subscriptions->pluck('user_id')->toArray();
            $userIds = array_map('strval', $userIds);

            $data = [
                'messages'          => $messagesData,
                'is_group'          => 0,
                'conversation_name' => '',
                'is_show'           => 1,
                'time_stamp'        => $room->updated_at->timestamp,
                'conversation_id'   => $room->id,
                'metafoxUserIds'    => $userIds,
                'users'             => $userIds,
            ];

            //sleep 5 seconds
            app('events')->dispatch('chatplus.job.on_import_conversation', [$data]);

            sleep(5);
        }
    }
}
