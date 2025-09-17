/**
 * @type: saga
 * name: updatedPhotoAlbum
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* updatedPhotoAlbum({ payload: { id } }: LocalAction<{ id: string }>) {
  yield put({
    type: 'photo/photo_album/RELOAD',
    payload: {
      photo_album: id
    }
  });

  yield* viewItem('photo', 'photo_album', id);
}

const sagas = [takeEvery('@updatedItem/photo_album', updatedPhotoAlbum)];

export default sagas;
