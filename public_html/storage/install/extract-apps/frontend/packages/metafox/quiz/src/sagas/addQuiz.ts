/**
 * @type: saga
 * name: addQuiz
 */

import { getGlobalContext, getItem, LocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* addQuiz({
  payload: { identity }
}: LocalAction<{ identity: string }>) {
  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  const pathname = '/quiz/add';

  navigate({
    pathname,
    search: `?module_id=${item.module_name}&item_id=${item.id}`
  });
}

const sagas = [takeEvery('addQuiz', addQuiz)];

export default sagas;
