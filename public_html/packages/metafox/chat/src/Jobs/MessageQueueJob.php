<?php

namespace MetaFox\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Broadcasting\UserMessage;
use MetaFox\Chat\Models\Message;

class MessageQueueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected Message $message, protected int $userId, protected string $broadcastType = Message::MESSAGE_CREATE, protected ?string $tempId = null)
    {
    }

    public function handle()
    {
        broadcast(new UserMessage($this->message, $this->userId, $this->broadcastType, $this->tempId));
    }
}
