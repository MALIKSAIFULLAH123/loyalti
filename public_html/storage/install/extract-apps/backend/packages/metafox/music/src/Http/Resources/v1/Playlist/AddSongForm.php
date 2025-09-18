<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AddSongForm.
 */
class AddSongForm extends AbstractForm
{
    /**
     * @var Model
     */
    public $resource;

    private string $itemId;
    private array $playlistIds = [];

    /**
     * @param Request $request
     */
    public function boot(Request $request): void
    {
        $this->itemId = $request->get('item_id', 0);

        $song              = resolve(SongRepositoryInterface::class)->find($this->itemId);
        $this->playlistIds = $song->playlists->pluck('id')->toArray();
    }

    protected function prepare(): void
    {
        $this->title(__p('music::phrase.add_to_playlist'))
            ->action('music/playlist/add-song')
            ->asPost()
            ->setValue([
                'item_id'      => $this->itemId,
                'playlist_ids' => $this->playlistIds,
            ]);
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $playlists = $this->getPlaylistOptions();

        if (!count($playlists)) {
            $basic->addField(
                Builder::typography('description')
                    ->plainText(__p('music::phrase.no_playlists_available', [
                        'link' => url_utility()->makeApiFullUrl('music/playlist/add'),
                        'can'  => (int) user()->hasPermissionTo('music_playlist.create'),
                    ])),
            );

            return;
        }

        $basic->addFields(
            Builder::choice('playlist_ids')
                ->disableClearable()
                ->multiple(true)
                ->options($playlists)
                ->label(__p('music::phrase.choose_playlist'))
                ->valueType('numeric'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit('submit')
                    ->label(__p('core::phrase.submit'))
                    ->disableWhenClean()
            );
    }

    /**
     * @return array<int,              mixed>
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function getPlaylistOptions(): array
    {
        return resolve(PlaylistRepositoryInterface::class)->getPlaylistOptions(user());
    }
}
