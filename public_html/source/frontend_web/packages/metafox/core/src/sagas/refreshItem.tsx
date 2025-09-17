/**
 * @type: saga
 * name: core.refreshItem
 */
import {
  deleteEntityAction,
  ENTITY_REFRESH,
  FETCH_DETAIL,
  getGlobalContext,
  getItem,
  getResourceAction,
  ItemLocalAction
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { put, takeEvery } from 'redux-saga/effects';

function* refreshItem(action: ItemLocalAction) {
  const { dispatch } = yield* getGlobalContext();
  const { identity } = action.payload;

  try {
    const id = identity.split('.')[3];

    const item = yield* getItem(identity);
    const config = yield* getResourceAction(
      item.module_name,
      item.resource_name,
      'viewItem'
    );

    const apiUrl = compactUrl(config.apiUrl, { id });

    yield put({
      type: FETCH_DETAIL,
      payload: { apiUrl },
      meta: {
        onFailure: () => dispatch(deleteEntityAction(identity)),
        onSuccess: action?.meta?.onSuccess
      }
    });
  } catch (err) {}
}

const sagas = [takeEvery(ENTITY_REFRESH, refreshItem)];

export default sagas;
