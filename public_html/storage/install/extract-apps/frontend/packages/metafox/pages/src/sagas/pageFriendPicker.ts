/**
 * @type: saga
 * name: page.saga.getMember
 */

import {
  getResourceAction,
  getItem,
  ItemLocalAction
} from '@metafox/framework';
import { put, takeLatest } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_PAGE } from '@metafox/pages';

function* getMember({
  payload
}: ItemLocalAction<{
  userIdentity: string;
}>) {
  const { userIdentity } = payload || {};
  const user = yield* getItem(userIdentity);
  const dataSource = yield* getResourceAction(
    APP_PAGE,
    RESOURCE_PAGE,
    user?.module_name === 'user'
      ? 'getForTagFriendsInFeed'
      : 'getForTagMembersInFeed'
  );

  yield put({
    type: 'page/saga/pickerMember/response',
    payload: { dataSource }
  });
}

export default takeLatest('page/saga/pickerMember/get', getMember);
