<?php

namespace MetaFox\Event\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Event\Contracts\ExporterContract;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\User;

/**
 * @see \MetaFox\Event\Support\Exporter
 * @method static string       export(User $user, Event $event)
 * @method static string       putFile(string $fileName, mixed $content)
 * @method static string       getFileName(int $userId)
 */
class Exporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ExporterContract::class;
    }
}
