<?php

namespace MetaFox\Invite\Support\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use MetaFox\Invite\Support\Invite as SupportInvite;

/**
 * Class Invite.
 *
 * @method static void sendMail(Model $model)
 * @method static void sendSMS(Model $model)
 * @method static void send(Model $model)
 * @method static array getStatusOptions()
 * @method static array getStatusRules()
 * @method static string getStatusPhrase(int $statusId)
 * @method static array getStatusInfo(int $statusId)
 * @see \MetaFox\Contact\Support\Contact
 */
class Invite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportInvite::class;
    }
}
