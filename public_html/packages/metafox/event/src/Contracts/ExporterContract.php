<?php

namespace MetaFox\Event\Contracts;

use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\User;

interface ExporterContract
{
    /**
     * @param User  $user
     * @param Event $model
     * @return string
     */
    public function export(User $user, Event $model): string;

    /**
     * @param string $fileName
     * @param mixed  $content
     * @return string
     */
    public function putFile(string $fileName, mixed $content): string;

    /**
     * @param int $userId
     * @return string
     */
    public function getFileName(int $userId): string;
}
