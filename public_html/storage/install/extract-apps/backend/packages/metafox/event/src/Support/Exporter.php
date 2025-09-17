<?php

namespace MetaFox\Event\Support;

use DateTime;
use MetaFox\Event\Contracts\ExporterContract;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Support\Export\Calendar;
use MetaFox\Platform\Contracts\User;

class Exporter implements ExporterContract
{
    /**
     * @throws \Exception
     */
    public function export(User $user, Event $model): string
    {
        $prodId   = config('app.name');
        $desc     = config('app.site_title');
        $userName = $user->full_name;
        $uid      = sprintf('%s@%s', md5($model->entityType() . $model->entityId()), strtolower(config('app.name')));
        $event    = new Export\Event();
        $calendar = new Calendar();

        $event->name($model->toTitle())
            ->createdAt(new DateTime($model->created_at))
            ->startsAt(new DateTime($model->start_time))
            ->endsAt(new DateTime($model->end_time))
            ->status($model->getStatus())
            ->description($model->getDescription())
            ->address(is_string($model->location_address) ? $model->location_address : $model->location_name)
            ->addressName(is_string($model->location_address) ? $model->location_name : $model->country_iso)
            ->coordinates(lat: $model->location_latitude, lng: $model->location_longitude)
            ->uniqueIdentifier($uid)
            ->transparent();

        return $calendar
            ->event(event: $event)
            ->productIdentifier($prodId)
            ->description($desc)
            ->name($userName)
            ->get();
    }

    public function putFile(string $fileName, mixed $content): string
    {
        file_put_contents($fileName, $content);

        return $fileName;
    }

    public function getFileName(int $userId): string
    {
        $date = new DateTime();
        return storage_path(sprintf('framework/export_event_%s_%s.ics', $userId, $date->format('m-d')));
    }
}
