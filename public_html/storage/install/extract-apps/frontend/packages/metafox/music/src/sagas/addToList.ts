/**
 * @type: saga
 * name: music.saga.addToList
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
  handleActionFeedback,
  patchEntity
} from '@metafox/framework';
import { difference } from 'lodash';
import { takeEvery } from 'redux-saga/effects';
import { APP_MUSIC, RESOURCE_PLAYLIST } from '../constant';

function* moveThread({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'addToPlaylist');

  if (!dataSource?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { item_id: item.id }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* removeFromPlayList({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  const { getPageParams, apiClient, compactUrl } = yield* getGlobalContext();

  const pageParams = getPageParams();
  const playlist_id = pageParams?.id;
  const identityPlaylist = `${APP_MUSIC}.entities.${RESOURCE_PLAYLIST}.${playlist_id}`;
  const playlist = yield* getItem(identityPlaylist);
  const config = yield* getItemActionConfig(item, 'removeFromPlaylist');
  const { songs } = playlist;

  if (!item) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, {
        ...item,
        playlist_id
      }),
      method: config.apiMethod
    });

    const result = response?.data?.data;
    const { statistic } = result;

    yield* patchEntity(identityPlaylist, {
      songs: difference(songs, [identity]),
      statistic
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* redirectToAllSongs({ payload }: ItemLocalAction) {
  const { navigate } = yield* getGlobalContext();
  navigate('music/song');
}

function* redirectToEditAlbum({ payload }: ItemLocalAction) {
  const { navigate } = yield* getGlobalContext();
  const { getPageParams } = yield* getGlobalContext();

  const pageParams = getPageParams();
  const album_id = pageParams?.id;
  navigate(`music/album/edit/${album_id}`);
}

const sagas = [
  takeEvery('music/addToPlaylist', moveThread),
  takeEvery('music/removeFromPlaylist', removeFromPlayList),
  takeEvery('music/redirectToAllSongs', redirectToAllSongs),
  takeEvery('music/redirectToEditAlbum', redirectToEditAlbum)
];

export default sagas;
