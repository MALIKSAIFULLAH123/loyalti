/**
 * @type: saga
 * name: advertise.saga.updatedItem
 * bundle: web
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updatedItem({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('advertise', 'advertise', id);
}

const sagas = [takeEvery('@updatedItem/advertise', updatedItem)];

export default sagas;
