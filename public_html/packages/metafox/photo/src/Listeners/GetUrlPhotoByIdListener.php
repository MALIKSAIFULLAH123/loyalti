<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class GetUrlPhotoByIdListener
{
    protected PhotoRepositoryInterface $repository;

    public function __construct(PhotoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  int     $id
     * @param  string  $type
     * @return array|null
     */
    public function handle(int $id, string $type): ?array
    {
        if ($type != Photo::ENTITY_TYPE) {
            return null;
        }

        $photo = $this->repository->getModel()->newQuery()->where('id', $id)->first();
        if ($photo instanceof Photo) {
            return [
                'phrase'    => null,
                'image_url' => collect($photo->images)->first(),
            ];
        }
        return [
            'phrase'    => __p('photo::phrase.attachment_are_not_showing'),
            'image_url' => null,
        ];
    }
}
