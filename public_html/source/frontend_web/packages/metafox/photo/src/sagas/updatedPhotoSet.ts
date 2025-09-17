/**
 * @type: saga
 * name: updatedPhotoSet
 */

import { getGlobalContext } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updatedPhotoSet() {
  const { navigate } = yield* getGlobalContext();
  navigate('/photo/my');
}

const sagas = [takeEvery('@updatedItem/photo_set', updatedPhotoSet)];

export default sagas;
