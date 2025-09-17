/**
 * @type: saga
 * name: core.fetchDetailSaga
 */
import {
  ABORT_CONTROL_START,
  FetchActionShape,
  FETCH_DETAIL,
  fulfillEntity,
  getGlobalContext
} from '@metafox/framework';
import axios from 'axios';
import { isFunction, omit } from 'lodash';
import { put, takeEvery } from 'redux-saga/effects';

export type FetchDetailConfig = {
  apiUrl: string;
  apiParams?: any;
  pageParams?: any;
  exceptEntities?: string[];
};

export function* fetchDetailSaga({
  payload: { apiUrl, apiParams, exceptEntities = [] },
  meta
}: FetchActionShape): Generator<unknown, string[], any> {
  const { apiClient, normalization } = yield* getGlobalContext();

  if (!apiUrl) return;

  try {
    const source = axios.CancelToken.source();

    if (meta?.abortId) {
      yield put({
        type: ABORT_CONTROL_START,
        payload: { abortId: meta.abortId, source }
      });
    }

    const response = yield apiClient.request({
      url: apiUrl,
      params: apiParams,
      cancelToken: source.token
    });

    const data = JSON.stringify(response?.data?.data);
    const result = normalization.normalize({
      ...JSON.parse(data),
      _loadedDetail: true
    });
    yield* fulfillEntity(omit(result.data, exceptEntities));

    isFunction(meta?.onSuccess) && meta.onSuccess();

    return result.ids;
  } catch (err) {
    const cancelled = axios.isCancel(err);

    if (cancelled) {
      return;
    }

    // prevent call when cancelled
    if (meta?.onFailure) {
      meta.onFailure(err);
    }
  }
}

const sagaEffect = takeEvery(FETCH_DETAIL, fetchDetailSaga);

export default sagaEffect;
