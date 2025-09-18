/**
 * @type: saga
 * name: addPoll
 */

import { getGlobalContext, getItem, LocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* addPoll({
  payload: { identity }
}: LocalAction<{ identity: string }>) {
  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  const pathname = '/poll/add';

  navigate({
    pathname,
    search: `?module_id=${item.module_name}&item_id=${item.id}`
  });
}

const sagas = [takeEvery('addPoll', addPoll)];

export default sagas;
