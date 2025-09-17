<?php

namespace MetaFox\Firebase\Channels;

class FirebaseWebChannel extends FirebaseChannel
{
    public function getChannelClass(): string
    {
        return \MetaFox\Firebase\Channels\v1\FirebaseWebChannel::class;
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'toMobileMessage',
        ];
    }
}
