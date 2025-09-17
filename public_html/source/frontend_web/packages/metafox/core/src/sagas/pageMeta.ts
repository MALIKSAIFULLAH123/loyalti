/**
 * @type: saga
 * name: core.pageMeta
 */
import {
  getGlobalContext,
  getPageMetaDataSelector,
  LOAD_PAGE_META,
  LocalAction
} from '@metafox/framework';
import {
  RELOAD_PAGE_META,
  IS_ADMINCP,
  IS_INSTALLATION
} from '@metafox/framework/constants';
import { put, select, takeLatest } from 'redux-saga/effects';
import qs from 'query-string';

interface PayloadMetaProps {
  pathname: string;
  queryParams?: Record<string, any>;
  href?: string;
  signal?: any;
}

function* fetchMetaData({
  pathname,
  queryParams,
  href,
  signal
}: PayloadMetaProps) {
  const { apiClient } = yield* getGlobalContext();

  if (!pathname) return;

  try {
    const data = yield apiClient
      .request({
        url: 'seo/meta',
        method: 'post',
        data: {
          url: pathname.replace(/^\/|\/$/g, ''),
          resolution: IS_ADMINCP ? 'admin' : 'web',
          queryParams
        },
        signal
      })
      .then(x => x.data?.data);

    yield put({
      type: 'pageMeta/put',
      payload: { id: href || pathname, data }
    });
  } catch (err) {
    //
  }
}

export function* loadPageMeta({
  payload: { pathname, queryParams, href, signal }
}: LocalAction<PayloadMetaProps>) {
  try {
    if (!pathname) return;

    if (IS_INSTALLATION) {
      return;
    }

    // check cached
    const data = yield select(getPageMetaDataSelector, href);

    if (data) return;

    yield* fetchMetaData({ pathname, queryParams, href, signal });
  } catch (err) {
    //
  }
}

export function* reloadCurrentPageMeta({ payload }: LocalAction) {
  try {
    const { location } = yield* getGlobalContext();

    const { state, pathname: _pathname, search: _search } = location;
    const pathname = state?.as || _pathname;
    const queryParams = _search ? qs.parse(_search.replace(/^\?/, '')) : {};
    const href = `${_pathname}${_search}`;

    if (!pathname) return;

    yield* fetchMetaData({ pathname, queryParams, href });
  } catch (err) {
    //
  }
}

const sagas = [
  takeLatest(LOAD_PAGE_META, loadPageMeta),
  takeLatest(RELOAD_PAGE_META, reloadCurrentPageMeta)
];

export default sagas;
