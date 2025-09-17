<?php

namespace MetaFox\Chat\Broadcasting;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Http\Resources\v1\Message\MessageAttachmentCollection;
use MetaFox\Chat\Models\Message;
use MetaFox\Chat\Traits\MessageTraits;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItem;

class UserMessage implements ShouldBroadcast
{
    use SerializesModels;
    use Dispatchable;
    use MessageTraits;

    public bool $afterCommit = true;

    /**
     * @param Message     $message
     * @param int         $userId
     * @param string      $broadcastType
     * @param string|null $tempId
     */
    public function __construct(private Message $message, private  int $userId, private  string $broadcastType, private ?string $tempId)
    {
    }

    public function broadcastOn()
    {
        return 'user.' . $this->userId;
    }

    /**
     * Event name for client to register.
     * @return string
     */
    public function broadcastAs()
    {
        switch ($this->broadcastType) {
            case Message::MESSAGE_REACT:
            case Message::MESSAGE_UPDATE:
                $type = 'MessageUpdate';
                break;
            default:
                $type = 'UserMessage';
        }

        return $type;
    }

    /**
     * Data to send to client.
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'module_name'   => 'chat',
            'resource_name' => 'message',
            'message_id'    => $this->message->id,
            'message'       => $this->message->message,
            'room_id'       => $this->message->room_id,
            'user_id'       => $this->message->userId(),
            'user'          => new UserEntityItem($this->message->userEntity),
            'type'          => $this->message->type,
            'extra'         => $this->processExtraMessage($this->message->extra),
            'attachments'   => new MessageAttachmentCollection($this->message->attachments),
            'reactions'     => $this->normalizeReactions($this->message->reactions),
            'tempId'        => $this->tempId,
            'created_at'    => $this->message->created_at,
            'updated_at'    => $this->message->updated_at,
        ];
    }
}
