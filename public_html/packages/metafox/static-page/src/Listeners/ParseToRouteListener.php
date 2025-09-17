<?php

namespace MetaFox\StaticPage\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\StaticPage\Models\StaticPage;

class ParseToRouteListener
{
    /**
     * @param string $url
     *
     * @return array<string,mixed>|null|void
     */
    public function handle(string $url)
    {
        try {
            $parts       = parse_url($url);
            $path        = Arr::get($parts, 'path', MetaFoxConstant::EMPTY_STRING);

            /** @var StaticPage $page */
            $id = StaticPage::query()->whereRaw('lower(slug)=lower(?)', $path)->value('id');

            if (!$id) {
                return null;
            }

            return [
                'path' => '/static-page/' . $id,
            ];
        } catch (\Exception $exception) {
            // do nothing
        }
    }
}
