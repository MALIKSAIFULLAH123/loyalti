/**
 * @type: saga
 * name: sevent.saga.updatedItem
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* updatedItem({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('sevent', 'sevent', id);
}

function* seventActive({ payload }) {
  yield put({ type: 'sevent/active', payload });
}

function* updatedTicketItem({ payload: { sevent_id } }: LocalAction<{ sevent_id: string }>) {
  console.log(sevent_id);
  yield* viewItem('sevent', 'sevent', sevent_id);
}

const sagas = [
  takeEvery('@updatedItem/sevent', updatedItem),
  takeEvery('sevent/hover', seventActive),
  takeEvery('@updatedItem/sevent_ticket', updatedTicketItem)
];

export default sagas;
