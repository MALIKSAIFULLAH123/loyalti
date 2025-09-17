/**
 * @type: saga
 * name: updatedPhoto
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_PHOTO, RESOURCE_PHOTO } from '@metafox/photo';

function* updatedPhoto({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem(APP_PHOTO, RESOURCE_PHOTO, id, { replace: true });
}

const sagas = [takeEvery('@updatedItem/photo', updatedPhoto)];

export default sagas;
