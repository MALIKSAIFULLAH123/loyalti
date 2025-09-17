/**
 * @type: saga
 * name: photo.saga.deletePhoto
 */

import {
  ENTITY_REFRESH,
  fulfillEntity,
  getGlobalContext
} from '@metafox/framework';
import { put, takeEvery } from 'redux-saga/effects';

function* deletePhotoDone(action: {
  payload: {
    feed_id: string;
    album?: any;
  };
}) {
  const { feed_id, album } = action.payload;
  const { normalization } = yield* getGlobalContext();

  if (album) {
    const result = yield normalization.normalize(album);

    yield* fulfillEntity(result.data);
  }

  if (feed_id) {
    yield put({
      type: ENTITY_REFRESH,
      payload: { identity: `feed.entities.feed.${feed_id}` }
    });
  }
}

const sagas = [takeEvery('photo/photo/deleteItem/DONE', deletePhotoDone)];

export default sagas;
