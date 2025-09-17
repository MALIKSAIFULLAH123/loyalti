/**
 * @type: saga
 * name: core.paginationSaga
 */
import {
  ABORT_CONTROL_START,
  getGlobalContext,
  getPagingSelector,
  initPagingState,
  LocalAction,
  PAGINATION,
  PAGINATION_UPDATE_LAST_READ,
  PAGINATION_FAILED,
  PAGINATION_INIT,
  PAGINATION_START,
  PAGINATION_SUCCESS,
  PagingMeta,
  PagingPayload,
  PagingState,
  getResourceAction,
  getItem,
  RemoteDataSource,
  getSession
} from '@metafox/framework';
import axios from 'axios';
import { uniq, omit, isEmpty, isNumber } from 'lodash';
import { put, select, takeEvery, takeLatest } from 'redux-saga/effects';
import qs from 'querystring';

export type FetchAction = LocalAction<PagingPayload, PagingMeta>;

export function* updateLastReadSaga({ payload }) {
  const { user } = yield* getSession();

  if (!user?.id) return;

  const { pagingId } = payload;
  const { apiClient, compactUrl, compactData, getPageParams } =
    yield* getGlobalContext();
  const pageParams = getPageParams();
  const itemPagination: any = yield* getItem(`pagination.${pagingId}`);
  const { appName, resourceName } = pageParams;
  const dataSource: RemoteDataSource = yield* getResourceAction(
    appName,
    resourceName,
    'updateLastRead'
  );

  if (!dataSource?.apiUrl || !pageParams?.page || !itemPagination?.pages)
    return;

  const identityItems = itemPagination.pages[pageParams?.page]?.ids;

  if (identityItems && identityItems.length) {
    const identityItem = identityItems[identityItems.length - 1];
    const item = yield* getItem(identityItem);

    try {
      yield apiClient.request({
        method: dataSource.apiMethod || 'patch',
        url: compactUrl(dataSource.apiUrl, pageParams),
        data: compactData(dataSource.apiParams, item)
      });

      return true;
    } catch (error) {
      return false;
    }
  }
}

export function* fetchPaginationSaga({ type, payload, meta }: FetchAction) {
  const {
    apiUrl,
    apiParams,
    pagingId,
    isLoadMorePagination,
    isLoadMoreButton,
    canLoadMore,
    numberOfItemsPerPage,
    lastIdMode,
    lastReadMode: lastReadModeDefault,
    maxPageNumber
  } = payload;

  try {
    const { apiClient, normalization, getPageParams, navigate } =
      yield* getGlobalContext();
    const pageParams = getPageParams();
    const limit = apiParams?.limit || numberOfItemsPerPage;
    const lastReadMode =
      !pageParams?.page && lastReadModeDefault && isLoadMorePagination;

    if (!pagingId) return;

    let state: PagingState = yield select(state =>
      getPagingSelector(state, pagingId)
    );

    if (!state) {
      state = initPagingState();
    }

    const pageState = canLoadMore
      ? (parseInt(state?.page) || 0) + 1
      : undefined;

    const page = isLoadMorePagination ? pageParams?.page : pageState;

    if (
      !isLoadMorePagination &&
      isNumber(maxPageNumber) &&
      maxPageNumber !== 0 &&
      pageState > maxPageNumber
    )
      return;

    const isLoadedDataPagination =
      isLoadMorePagination &&
      !lastReadMode &&
      state?.pages[page || 1] &&
      !state?.refreshing;

    if (state?.loading || state?.ended || isLoadedDataPagination) return;

    if (PAGINATION_INIT === type && state?.initialized) {
      return;
    }

    const source = axios.CancelToken.source();

    yield put({
      type: PAGINATION_START,
      payload: { paging: { pagingId, page } }
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
        page: !lastIdMode ? page : undefined,
        limit: limit ? limit : undefined,
        view: lastReadMode ? 'continue_last_read' : apiParams?.view
      },
      cancelToken: source.token
    });
    const data = response.data?.data;

    const links = response.data?.links;
    const offset = response.data?.pagination || {};
    const noResultProps =
      response.data?.no_result || response.data?.meta?.no_result || {};
    const pagesOffset = response.data?.meta || {};
    const result = normalization.normalize(data);
    const nextState: PagingState = yield select(
      state => state.pagination[pagingId]
    );
    const ids = uniq((nextState.ids ?? []).concat(result.ids));

    if (isLoadMorePagination && !data.length && pagesOffset?.total) {
      const total_pages = Math.ceil(pagesOffset?.total / pagesOffset?.per_page);

      if (total_pages) {
        navigate(
          {
            search: qs.stringify({
              page: total_pages
            })
          },
          { replace: true }
        );
      }
    }

    const pages = isLoadMorePagination
      ? {
          [pagesOffset.current_page || page]: {
            ids: result.ids,
            from: pagesOffset?.from,
            to: pagesOffset?.to
          }
        }
      : {};

    let ended =
      0 === result.ids.length ||
      (limit && result.ids.length < limit) ||
      (pagesOffset?.total && ids.length >= pagesOffset?.total);

    if (isLoadMoreButton && !ended) {
      ended = links && isEmpty(links?.next);
    }

    if (isLoadMorePagination) {
      ended = pagesOffset?.total > 0 ? undefined : true;
    }

    if (maxPageNumber && page === maxPageNumber) {
      ended = true;
    }

    const paginationType = isLoadMorePagination ? 'pagination' : 'loadmore';

    const paging = {
      ids,
      pagingId,
      page: pagesOffset?.current_page || page,
      ended,
      offset,
      pagesOffset: omit(pagesOffset, ['from', 'to']),
      pages,
      noResultProps,
      paginationType
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

    if (isLoadMorePagination && lastReadModeDefault) {
      yield put({
        type: PAGINATION_UPDATE_LAST_READ,
        payload: {
          pagingId
        }
      });
    }
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
  takeEvery([PAGINATION, PAGINATION_INIT], fetchPaginationSaga),
  takeLatest(PAGINATION_UPDATE_LAST_READ, updateLastReadSaga)
];

export default sagaEffect;
