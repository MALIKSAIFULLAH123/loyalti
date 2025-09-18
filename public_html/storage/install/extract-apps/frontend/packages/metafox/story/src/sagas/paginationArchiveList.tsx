/**
 * @type: saga
 * name: story.paginationArchiveSaga
 */
import {
  ABORT_CONTROL_START,
  getGlobalContext,
  getItem,
  getPagingSelector,
  initPagingState,
  LocalAction,
  PAGINATION_CLEAR,
  PAGINATION_DELETE,
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
import {
  prefixPagingId_archive,
  STORY_ARCHIVE_PAGINATION,
  STORY_ARCHIVE_PAGINATION_DELETE,
  STORY_ARCHIVE_PAGINATION_INIT,
  STORY_CLEAR_PAGINATION_NOFULL
} from '../constants';
import { getPagingArchiveNoEndedSelector } from '../selectors';

type PagingPayloadExtend = PagingPayload & {
  direction: 'next' | 'prev';
  identity?: string;
};
type PagingMetaExtend = PagingMeta & { onSuccess?: any; onError?: any };

export type FetchAction = LocalAction<PagingPayloadExtend, PagingMetaExtend>;

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

const handlePushStory = (data, ids, direction) =>
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

    if (STORY_ARCHIVE_PAGINATION_INIT === type && state?.initialized) {
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

    const ids = handlePushStory(state.ids ?? [], result.ids, direction);
    const ended = !pagesOffset.total || ids?.length >= pagesOffset.total;
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
    onSuccess && onSuccess(paging);
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

export function* paginationDeleteArchive({ type, payload, meta }: FetchAction) {
  const { identity, pagingId } = payload;
  const { onSuccess } = meta || {};

  try {
    const story = yield* getItem(identity);

    const state: PagingState = yield select(state =>
      getPagingSelector(state, pagingId)
    );

    if (!state || !story || !pagingId) return;

    if (state?.ids?.length === 1) {
      yield put({
        type: PAGINATION_CLEAR,
        payload: { pagingId }
      });

      onSuccess && onSuccess();

      return;
    }

    yield put({
      type: PAGINATION_DELETE,
      payload: {
        identity,
        prefixPagingId: prefixPagingId_archive
      }
    });

    const paging = {
      ...state,
      pagingId,
      pagesOffset: {
        ...state?.pagesOffset,
        total: state?.pagesOffset?.total - 1
      }
    };

    yield put({
      type: PAGINATION_SUCCESS,
      payload: { paging }
    });
    onSuccess && onSuccess();
  } catch (error) {
    onSuccess && onSuccess();
  }
}

export function* clearPaginationNoFull({ payload }: FetchAction) {
  const { dispatch } = yield* getGlobalContext();

  try {
    const state: PagingState[] = yield select(state =>
      getPagingArchiveNoEndedSelector(state, prefixPagingId_archive)
    );

    if (!state?.length) return;

    state.forEach(pagingId => {
      dispatch({
        type: PAGINATION_CLEAR,
        payload: { pagingId }
      });
    });
  } catch (err) {}
}

const sagaEffect = [
  takeEvery(
    [STORY_ARCHIVE_PAGINATION, STORY_ARCHIVE_PAGINATION_INIT],
    fetchPaginationSaga
  ),
  takeEvery(STORY_CLEAR_PAGINATION_NOFULL, clearPaginationNoFull),
  takeEvery(STORY_ARCHIVE_PAGINATION_DELETE, paginationDeleteArchive)
];

export default sagaEffect;
