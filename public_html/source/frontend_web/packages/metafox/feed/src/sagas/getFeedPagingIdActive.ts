import { select, put } from 'redux-saga/effects';
import { PAGING_REFRESH_ACTION } from '@metafox/feed';

type TypeDataRefresh = {
  excludePagingId?: string;
};

export default function* getFeedPagingIdActive() {
  return yield select(state => state.feed.paging.active);
}

export function* putPagingFeedRefresh(data: TypeDataRefresh = {}) {
  yield put({
    type: PAGING_REFRESH_ACTION,
    payload: data
  });
}
