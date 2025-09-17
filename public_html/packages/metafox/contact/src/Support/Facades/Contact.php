<?php

namespace MetaFox\Contact\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Contact\Support\Contact as SupportContact;

/**
 * Class Contact.
 *
 * @method static void send(array $params = [])
 * @see \MetaFox\Contact\Support\Contact
 */
class Contact extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportContact::class;
    }
}
