<?php

namespace MetaFox\StaticPage\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\StaticPage\Repositories\StaticPageContentRepositoryInterface;
use MetaFox\StaticPage\Models\StaticPageContent as Model;

/**
 * Class StaticPageContentRepository.
 */
class StaticPageContentRepository extends AbstractRepository implements StaticPageContentRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreateContent(StaticPage $staticPage, array $attributes): bool
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
                    'static_page_id' => $staticPage->entityId(),
                    'locale'         => $locale,
                ],
                [
                    'text' => parse_input()->prepare($content),
                ]
            );
        }

        return true;
    }
}
