/**
 * @type: saga
 * name: photo.paginationSaga
 */
import {
  ABORT_CONTROL_START,
  getGlobalContext,
  getPagingSelector,
  initPagingState,
  LocalAction,
  PAGINATION_FAILED,
  PAGINATION_START,
  PAGINATION_SUCCESS,
  PagingMeta,
  PagingPayload,
  PagingState
} from '@metafox/framework';
import axios from 'axios';
import { uniq, omit } from 'lodash';
import { put, select, takeEvery } from 'redux-saga/effects';
import { PHOTO_PAGINATION, PHOTO_PAGINATION_INIT } from '@metafox/photo';

type PagingPayloadExtend = PagingPayload & { direction: 'next' | 'prev' };

export type FetchAction = LocalAction<PagingPayloadExtend, PagingMeta>;

const getPage = (direction, state) => {
  switch (direction) {
    case 'next':
      return state?.pagesOffset?.next_page;
    case 'prev':
      return state?.pagesOffset?.prev_page;
    default:
      return undefined;
  }
};

const makePageUpdate = (direction, data) => {
  switch (direction) {
    case 'next':
      return { next_page: data.current_page + 1 };
    case 'prev':
      return { prev_page: data.current_page - 1 };
    default:
      return {
        next_page: data.current_page + 1,
        prev_page: data.current_page - 1
      };
  }
};

const getEndedByDirection = (direction, state) => {
  if (!direction) return false;

  switch (direction) {
    case 'next':
      return state?.pagesOffset?.next_page > state?.pagesOffset?.last_page;
    case 'prev':
      return state?.pagesOffset?.prev_page <= 0;
    default:
      return true;
  }
};

const handlePushPhoto = (data, ids, direction) =>
  uniq(direction === 'prev' ? [...ids, ...data] : [...data, ...ids]);

export function* fetchPaginationSaga({ type, payload, meta }: FetchAction) {
  const { apiUrl, apiParams, pagingId, direction } = payload;
  const { onSuccess, onError } = meta || {};

  try {
    const { apiClient, normalization } = yield* getGlobalContext();

    if (!pagingId) return;

    let state: PagingState = yield select(state =>
      getPagingSelector(state, pagingId)
    );

    if (!state) {
      state = initPagingState();
    }

    if (state?.loading || state?.ended || getEndedByDirection(direction, state))
      return;

    if (PHOTO_PAGINATION_INIT === type && state?.initialized) {
      return;
    }

    const source = axios.CancelToken.source();

    yield put({
      type: PAGINATION_START,
      payload: { paging: { pagingId } }
    });

    if (meta?.abortId) {
      yield put({
        type: ABORT_CONTROL_START,
        payload: { abortId: meta.abortId, source }
      });
    }

    const response = yield apiClient.request({
      url: apiUrl,
      params: {
        ...apiParams,
        ...state.offset,
        page: getPage(direction, state)
      },
      cancelToken: source.token
    });

    const data = response.data?.data;
    const offset = response.data?.pagination || {};
    const noResultProps =
      response.data?.no_result || response.data?.meta?.no_result || {};
    const metaResponse = response.data?.meta || {};
    const next_prev = makePageUpdate(direction, metaResponse);
    const pagesOffset = {
      ...state.pagesOffset,
      ...metaResponse,
      ...next_prev
    };
    const result = normalization.normalize(data);

    const ids = handlePushPhoto(state.ids ?? [], result.ids, direction);
    const ended = !pagesOffset.total || ids >= pagesOffset.total;
    const paging = {
      ids,
      pagingId,
      ended,
      offset,
      pagesOffset: omit(pagesOffset, ['from', 'to']),
      noResultProps
    };
    const successPayload = {
      data: result.data,
      paging
    };

    yield put({
      type: PAGINATION_SUCCESS,
      payload: successPayload
    });

    yield put({
      ...meta?.successAction,
      meta: { ...(meta?.successAction?.meta || {}), ...paging }
    });
    onSuccess && onSuccess();
  } catch (error) {
    // do not block ended when pagiation is block.
    const cancelled = axios.isCancel(error);

    if (!cancelled) {
      onError && onError(error);
    }

    yield put({
      type: PAGINATION_FAILED,
      payload: {
        paging: { pagingId, ended: !cancelled },
        error: cancelled ? undefined : error
      }
    });
  }
}

const sagaEffect = [
  takeEvery([PHOTO_PAGINATION, PHOTO_PAGINATION_INIT], fetchPaginationSaga)
];

export default sagaEffect;
