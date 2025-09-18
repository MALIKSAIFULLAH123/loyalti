<?php

namespace MetaFox\Giphy\Repositories\Eloquent;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use MetaFox\Giphy\Repositories\GifRepositoryInterface;
use MetaFox\Giphy\Supports\Helpers;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class GifRepository implements GifRepositoryInterface
{
    /**
     * @param  User  $user
     * @param  array $attributes
     * @return array
     */
    public function search(User $user, array $attributes): array
    {
        $cacheKey = 'giphy.search.' . md5(json_encode($attributes));

        return Cache::remember($cacheKey, 300, function () use ($user, $attributes) {
            return $this->searchGifGiphy($user, $attributes);
        });
    }

    /**
     * @param  User  $user
     * @param  array $attributes
     * @return array
     */
    public function trending(User $user, array $attributes): array
    {
        $cacheKey = 'giphy.trending.' . md5(json_encode($attributes));

        return Cache::remember($cacheKey, 300, function () use ($user, $attributes) {
            if (isset($attributes['q']) && $attributes['q'] !== '') { //Support for mobile, not actually search for trending
                return $this->searchGifGiphy($user, $attributes);
            }

            return $this->trendingGifGiphy($user, $attributes);
        });
    }

    /**
     * @param  User       $user
     * @param  string     $id
     * @return array|null
     */
    public function getGifData(User $user, string $id): ?array
    {
        $giphyApiUrl = Helpers::GIPHY_PUBLIC_API_URL . '/' . $id;

        $apiKey = Settings::get('giphy.giphy_api_key', '');

        if (empty($apiKey)) {
            return null;
        }

        $response = Http::timeout(60)->get($giphyApiUrl, [
            'api_key' => $apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data)) {
                return null;
            }

            $item = $this->processSingleResponseData($data);

            return [
                'title'        => $item['title'] ?? null,
                'giphy_gif_id' => $item['id'] ?? null,
                'url'          => $item['url'] ?? null,
                'embed_url'    => $item['embed_url'] ?? null,
                'slug'         => $item['slug'] ?? null,
                'rating'       => $item['rating'] ?? null,
                'images'       => $item['images'] ?? null,
            ];
        }

        return null;
    }

    private function searchGifGiphy(User $user, array $attributes): array
    {
        $giphyApiSearchUrl = Helpers::GIPHY_PUBLIC_API_URL . '/search';

        $apiKey = Settings::get('giphy.giphy_api_key', '');

        if (empty($apiKey)) {
            return [];
        }

        $attributes = array_merge($attributes, [
            'api_key' => $apiKey,
        ]);

        $response = Http::timeout(60)->get($giphyApiSearchUrl, $attributes);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data)) {
                return [];
            }

            return $this->processResponseData($data);
        }

        return [];
    }

    private function trendingGifGiphy(User $user, array $attributes): array
    {
        $giphyApiTrendingUrl = Helpers::GIPHY_PUBLIC_API_URL . '/trending';

        $apiKey = Settings::get('giphy.giphy_api_key', '');

        if (empty($apiKey)) {
            return [];
        }

        $attributes = array_merge($attributes, [
            'api_key' => $apiKey,
        ]);

        $response = Http::timeout(60)->get($giphyApiTrendingUrl, $attributes);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data)) {
                return [];
            }

            return $this->processResponseData($data);
        }

        return [];
    }

    private function processSingleResponseData($data): array
    {
        $item = $data['data'] ?? null;

        if (empty($item)) {
            return [];
        }

        if (isset($item['images'])) {
            $item['images'] = [
                'fixed_width'       => $item['images']['fixed_width'],
                'fixed_width_small' => $item['images']['fixed_width_small'],
                'original'          => $item['images']['original'],
            ];
        }

        return $item;
    }

    private function processResponseData($data): array
    {
        $items = $data['data'] ?? null;

        if (empty($items)) {
            return [];
        }

        foreach ($items as $key => $item) {
            if (isset($item['images'])) {
                $data['data'][$key]['images'] = [
                    'fixed_width'       => $item['images']['fixed_width'],
                    'fixed_width_small' => $item['images']['fixed_width_small'],
                    'original'          => $item['images']['original'],
                ];
            }
        }

        return $data;
    }
}
