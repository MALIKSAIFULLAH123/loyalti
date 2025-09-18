/**
 * @type: saga
 * name: updatedPoll
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updatedPoll({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('poll', 'poll', id);
}

const sagas = [takeEvery('@updatedItem/poll', updatedPoll)];

export default sagas;
