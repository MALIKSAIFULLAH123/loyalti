/**
 * @type: saga
 * name: updateGroups
 */

import {
  LocalAction,
  viewItem,
  PAGINATION_REFRESH,
  getGlobalContext,
  makeDirtyPaging,
  getItem,
  PagingState,
  getPagingSelector,
  getItemsSelector
} from '@metafox/framework';
import { takeEvery, select } from 'redux-saga/effects';
import { APP_GROUP } from '../constant';

function* updateGroups({ payload: { id } }: LocalAction<{ id: string }>) {
  const { dispatch } = yield* getGlobalContext();

  yield* viewItem('group', 'group', id);

  dispatch({
    type: PAGINATION_REFRESH,
    payload: { pagingId: '/user/shortcut' }
  });
}

function* deleteGroupDone(action: { payload: any }) {
  const item = action.payload;

  const itemOwner = yield* getItem(item?.parent_user);

  if (!item || !itemOwner?.id || itemOwner?.resource_name !== APP_GROUP) return;

  const pagingId = `groupAnnouncement/${itemOwner.id}`;

  const paging: PagingState = yield select(state =>
    getPagingSelector(state, pagingId)
  );

  const dataPaging: any = yield select(state =>
    getItemsSelector(state, paging?.ids || [])
  );

  const isExistAnnouncement = dataPaging.findIndex(
    data => data.item === item.identity
  );

  if (isExistAnnouncement === -1) return;

  yield* makeDirtyPaging('groupAnnouncement', 'feed', false);
}

const sagas = [
  takeEvery('@updatedItem/group', updateGroups),
  takeEvery('feed/feed/deleteItem/DONE', deleteGroupDone),
  takeEvery('feed/feed/removeFeed/DONE', deleteGroupDone)
];

export default sagas;
