<?php

namespace MetaFox\Firebase\Channels;

class FirebaseMobileChannel extends FirebaseChannel
{
    public function getChannelClass(): string
    {
        return \MetaFox\Firebase\Channels\v1\FirebaseMobileChannel::class;
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'toMobileMessage',
        ];
    }
}
