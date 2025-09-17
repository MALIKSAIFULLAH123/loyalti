<?php

namespace MetaFox\Announcement\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Announcement\Repositories\AnnouncementContentRepositoryInterface;
use MetaFox\Announcement\Models\AnnouncementContent as Model;

/**
 * Class AnnouncementContentRepository.
 */
class AnnouncementContentRepository extends AbstractRepository implements AnnouncementContentRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreateContent(Announcement $announcement, array $attributes): bool
    {
        $text = Arr::get($attributes, 'text') ?: [];

        if (!is_array($text)) {
            return false;
        }

        if (empty($text)) {
            return true;
        }

        foreach ($text as $locale => $content) {
            $this->getModel()->newModelQuery()->updateOrCreate(
                [
                    'announcement_id' => $announcement->entityId(),
                    'locale'          => $locale,
                ],
                [
                    'text'        => parse_input()->clean($content, false, true),
                    'text_parsed' => parse_input()->prepare($content, false, true),
                ]
            );
        }

        return true;
    }
}
