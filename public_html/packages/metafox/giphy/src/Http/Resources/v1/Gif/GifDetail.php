<?php

namespace MetaFox\Giphy\Http\Resources\v1\Gif;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GifDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->resource['id'] ?? null,
            'module_name'   => 'giphy',
            'resource_name' => 'gif',
            'type'          => $this->resource['type'] ?? null,
            'url'           => $this->resource['url'] ?? null,
            'giphy_gif_id'  => $this->resource['id'] ?? null,
            'slug'          => $this->resource['slug'] ?? null,
            'title'         => $this->resource['title'] ?? null,
            'rating'        => $this->resource['rating'] ?? null,
            'is_sticker'    => $this->resource['is_sticker'] ?? null,
            'embed_url'     => $this->resource['embed_url'] ?? null,
            'images'        => $this->resource['images'] ?? [],
        ];
    }
}
