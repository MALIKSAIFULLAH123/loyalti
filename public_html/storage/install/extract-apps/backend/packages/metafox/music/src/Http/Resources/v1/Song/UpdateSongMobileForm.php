<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Music\Http\Requests\v1\Song\CreateFormRequest;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateSongForm.
 * @property Model $resource
 */
class UpdateSongMobileForm extends StoreSongMobileForm
{
    /**
     * @param  CreateFormRequest       $request
     * @param  SongRepositoryInterface $repository
     * @param  int|null                $id
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, SongRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        policy_authorize(SongPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $privacy = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $genres = $this->resource->genres
            ->pluck('id')
            ->toArray();

        $this->title(__p('music::phrase.edit_song'))
            ->action(url_utility()->makeApiUrl('/music/song/' . $this->resource->entityId()))
            ->setBackProps(__p('core::web.music'))
            ->asPut()
            ->setValue([
                'name'         => $this->resource->name,
                'description'  => $this->resource->description,
                'genres'       => $genres,
                'privacy'      => $privacy,
                'owner_id'     => $this->resource->owner_id,
                'useThumbnail' => true,
                'module_id'    => $this->resource->entityType(),
                'album'        => $this->resource->album_id,
            ]);
    }
}
