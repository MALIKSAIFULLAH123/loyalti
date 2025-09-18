/**
 * @type: saga
 * name: forum_thread.saga.updatedItem
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_FORUM, RESOURCE_FORUM_THREAD } from '../constants';

function* updatedItem({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem(APP_FORUM, RESOURCE_FORUM_THREAD, id);
}

const sagas = [takeEvery('@updatedItem/forum_thread', updatedItem)];

export default sagas;
