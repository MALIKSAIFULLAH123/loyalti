/**
 * @type: saga
 * name: bgstatus.bootstrap
 */
import { getGlobalContext } from '@metafox/framework';
import { put, select, takeLatest } from 'redux-saga/effects';
import { getBgStatusSelector } from '@metafox/bgstatus/selectors';

const LIMIT = 10;

export function* loadMore() {
  const { ended, loading, page = 0 } = yield select(getBgStatusSelector);

  if (ended || loading) return;

  yield put({
    type: 'bgstatus/loadmore/loading'
  });
  const { apiClient } = yield* getGlobalContext();

  try {
    const pageState = page + 1;
    const response = yield apiClient.request({
      url: '/pstatusbg-collection',
      method: 'get',
      params: { page: pageState, limit: LIMIT }
    });

    const data = response.data?.data;

    yield put({
      type: 'bgstatus/ADD_COLLECTIONS',
      payload: {
        collections: data,
        page: pageState
      }
    });

    if (!data?.length || data?.length < LIMIT) {
      yield put({
        type: 'bgstatus/loadmore/end'
      });

      return;
    }
    // eslint-disable-next-line no-empty
  } catch (err) {}
}

const sagas = [takeLatest('bgstatus/LOAD', loadMore)];

export default sagas;
