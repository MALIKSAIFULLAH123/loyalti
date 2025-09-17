/**
 * @type: saga
 * name: saga.feed.pagingRefresh
 */

import { makeDirtyPaging, LocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { PAGING_REFRESH_ACTION } from '@metafox/feed';

function* refresh(action: LocalAction<{ excludePagingId?: string }>) {
  const { payload } = action || {};
  const { excludePagingId } = payload || {};
  yield* makeDirtyPaging('feed', excludePagingId);
}

const sagas = [takeEvery(PAGING_REFRESH_ACTION, refresh)];

export default sagas;
