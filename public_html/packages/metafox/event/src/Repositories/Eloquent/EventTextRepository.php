<?php

namespace MetaFox\Event\Repositories\Eloquent;

use MetaFox\Event\Models\EventText;
use MetaFox\Event\Repositories\EventTextRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

class EventTextRepository extends AbstractRepository implements EventTextRepositoryInterface
{
    public function model()
    {
        return EventText::class;
    }
}
