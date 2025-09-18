<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Music\Http\Requests\v1\Playlist\CreateFormRequest;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Policies\PlaylistPolicy;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdatePlaylistMobileForm.
 * @property Model $resource
 */
class UpdatePlaylistMobileForm extends StorePlaylistMobileForm
{
    /**
     * @param  CreateFormRequest           $request
     * @param  PlaylistRepositoryInterface $repository
     * @param  int|null                    $id
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, PlaylistRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        policy_authorize(PlaylistPolicy::class, 'update', $context, $this->resource);
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

        $this->title(__p('music::phrase.edit_playlist'))
            ->action(url_utility()->makeApiUrl('/music/playlist/' . $this->resource->entityId()))
            ->setBackProps(__p('core::web.music'))
            ->asPut()
            ->setValue([
                'name'         => $this->resource->name,
                'description'  => $this->resource->description,
                'privacy'      => $privacy,
                'owner_id'     => $this->resource->owner_id,
                'useThumbnail' => true,
                'module_id'    => $this->resource->entityType(),
            ]);
    }
}
