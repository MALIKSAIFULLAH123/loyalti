<?php

namespace MetaFox\TourGuide\Listeners;

class AllowEndpointsPendingSubscriptionListener
{
    public function handle(): ?array
    {
        return [
            'tour-guide\/actions',
        ];
    }
}
