/**
 * @type: saga
 * name: updateSongs
 */

import {
  LocalAction,
  viewItem,
  deleteEntity,
  patchEntity,
  getItem,
  makeDirtyPaging
} from '@metafox/framework';
import { ItemStatisticShape } from '@metafox/ui';
import { difference } from 'lodash';
import { takeEvery } from 'redux-saga/effects';
import { RESOURCE_SONG } from '../constant';

function* updateSongs({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('music', 'music_song', id);
}

function* updateAlbums({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* deleteEntity(`music.entities.music_album.${id}`);
  yield* viewItem('music', 'music_album', id);
}

function* updatePlaylists({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('music', 'music_playlist', id);
  yield* makeDirtyPaging('music/playlist');
}

function* deleteSongDone(action: {
  payload: {
    resource_name: string;
    statistic?: ItemStatisticShape;
    album_id?: string;
    module_name?: string;
  };
}) {
  const { resource_name, statistic, album_id, module_name, identity } = action.payload;

  if (resource_name === RESOURCE_SONG) return;

  const identityAlbum = `${module_name}.entities.${resource_name}.${album_id}`;
  const album = yield* getItem(identityAlbum);

  yield* patchEntity(identityAlbum, {
    statistic,
    songs: difference(album?.songs, [identity])
  });
}

const sagas = [
  takeEvery('@updatedItem/music_song', updateSongs),
  takeEvery('@updatedItem/music_album', updateAlbums),
  takeEvery('@updatedItem/music_playlist', updatePlaylists),
  takeEvery('music/music_song/deleteItem/DONE', deleteSongDone)
];

export default sagas;
