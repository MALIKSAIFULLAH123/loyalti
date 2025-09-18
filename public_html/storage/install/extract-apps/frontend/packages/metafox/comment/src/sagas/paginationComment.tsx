/**
 * @type: saga
 * name: comment.paginationSaga
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
  PAGINATION_CLEAR,
  PagingMeta,
  PagingPayload,
  PagingState,
  patchEntity,
  getItem
} from '@metafox/framework';
import axios from 'axios';
import { uniq, omit, isEmpty } from 'lodash';
import { put, select, takeEvery } from 'redux-saga/effects';
import {
  COMMENT_PAGINATION,
  COMMENT_PAGINATION_INIT,
  COMMENT_PAGINATION_CLEAR,
  COMMENT_PAGINATION_PREFIX
} from '@metafox/comment';

type PagingPayloadExtend = PagingPayload & { mode?: string };

export type FetchAction = LocalAction<PagingPayloadExtend, PagingMeta>;

export function* clearPagination({
  type,
  payload,
  meta
}: LocalAction<
  { identity: string; clearEntity?: string },
  { onSuccess?: () => void; onStart?: () => void }
>) {
  const { identity, clearEntity } = payload;
  const { onSuccess, onStart } = meta;
  const { parent_id } = yield* getItem(clearEntity);
  onStart && onStart();
  yield* patchEntity(identity, {
    relevant_comments: undefined
  });

  if (parent_id) {
    yield* patchEntity(`comment.entities.comment.${parent_id}`, {
      relevant_children: undefined
    });
  }

  yield put({
    type: PAGINATION_CLEAR,
    payload: { prefixPagingId: COMMENT_PAGINATION_PREFIX }
  });
  onSuccess && onSuccess();
}

export function* fetchPaginationSaga({ type, payload, meta }: FetchAction) {
  const { apiUrl, apiParams, pagingId, numberOfItemsPerPage, mode } = payload;

  try {
    const { apiClient, normalization } = yield* getGlobalContext();
    const limit = apiParams?.limit || numberOfItemsPerPage;

    if (!pagingId) return;

    let state: PagingState = yield select(state =>
      getPagingSelector(state, pagingId)
    );

    if (!state) {
      state = initPagingState();
    }

    if (state?.loading || state?.ended) return;

    if (COMMENT_PAGINATION_INIT === type && state?.initialized) {
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
        limit: limit ? limit : undefined,
        view: apiParams?.view
      },
      cancelToken: source.token
    });
    const data = response.data?.data;
    const offset = response.data?.pagination || {};
    const noResultProps =
      response.data?.no_result || response.data?.meta?.no_result || {};
    const pagesOffset = response.data?.meta || {};
    const result = normalization.normalize(data);
    const nextState: PagingState = yield select(
      state => state.pagination[pagingId]
    );
    const ids = uniq((nextState.ids ?? []).concat(result.ids));

    let ended =
      isEmpty(result.ids) || (result.ids?.length < limit && limit)
        ? true
        : false;

    if (mode === 'two_direction_load') {
      ended = false;
    }

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
      ...(meta?.successAction || {
        type: `${paging.pagingId.split('/')[0]}/LOAD_SUCCESS`
      }),
      meta: { ...(meta?.successAction?.meta || {}), ...paging }
    });
  } catch (error) {
    // do not block ended when pagiation is block.
    const cancelled = axios.isCancel(error);

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
  takeEvery([COMMENT_PAGINATION, COMMENT_PAGINATION_INIT], fetchPaginationSaga),
  takeEvery(COMMENT_PAGINATION_CLEAR, clearPagination)
];

export default sagaEffect;
