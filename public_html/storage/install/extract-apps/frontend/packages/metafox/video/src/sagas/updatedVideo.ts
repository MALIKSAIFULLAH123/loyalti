/**
 * @type: saga
 * name: updatedVideo
 */

import {
  ENTITY_REFRESH,
  getGlobalContext,
  PAGINATION_REFRESH,
  viewItem
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { VideoItemShape } from '../types';

function* updatedVideo(action: {
  payload: VideoItemShape & { id: string; owner_id?: string | number };
}) {
  const item = action.payload;

  const { navigate } = yield* getGlobalContext();

  if (item?.owner_id && item?.is_processing) {
    navigate(`${item?.owner?.resource_name}/${item?.owner?.id}/video`, {
      replace: true
    });

    return;
  }

  if (!item?.is_processing) {
    yield* viewItem('video', 'video', item.id, { replace: true });

    return;
  }

  navigate('/video/my');
}

function* deleteVideoDone(action: {
  payload: {
    feed_id: string;
    album_id?: string;
  };
}) {
  const { feed_id, album_id } = action.payload;

  if (album_id) {
    yield put({
      type: PAGINATION_REFRESH,
      payload: {
        apiUrl: `/photo/album/${album_id}`,
        apiParams: {
          view: 'latest'
        },
        pagingId: `photo-album/${album_id}`
      }
    });
  }

  if (feed_id) {
    yield put({
      type: ENTITY_REFRESH,
      payload: { identity: `feed.entities.feed.${feed_id}` }
    });
  }
}

const sagas = [
  takeEvery('@updatedItem/video', updatedVideo),
  takeEvery('video/video/deleteItem/DONE', deleteVideoDone)
];

export default sagas;
