<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Page\Support\PageSupport;

class SharedDataPreparationListener
{
    public function handle(string $postType, array $data): ?array
    {
        if (PageSupport::SHARED_TYPE !== $postType) {
            return null;
        }

        $pages = Arr::get($data, 'pages', []);

        if (is_array($pages) && count($pages)) {
            Arr::set($data, 'owners', $pages);

            Arr::set($data, 'success_message', __p('page::phrase.shared_to_your_page'));

            unset($data['pages']);
        }

        return $data;
    }
}
