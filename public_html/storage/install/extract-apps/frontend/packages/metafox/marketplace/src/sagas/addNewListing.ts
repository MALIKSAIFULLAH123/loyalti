/**
 * @type: saga
 * name: addNewListing
 */

import { getGlobalContext, getItem, LocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* addNewListing({
  payload: { identity }
}: LocalAction<{ identity: string }>) {
  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  if (!item) return;

  const pathname = '/marketplace/add';

  navigate({
    pathname,
    search: `?module_id=${item.module_name}&item_id=${item.id}`
  });
}

const sagas = [takeEvery('addNewListing', addNewListing)];

export default sagas;
