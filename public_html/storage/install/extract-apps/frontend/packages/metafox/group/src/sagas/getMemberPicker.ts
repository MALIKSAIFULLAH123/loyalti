/**
 * @type: saga
 * name: group.saga.getMember
 */

import {
  getResourceAction,
  ItemLocalAction,
  getItem
} from '@metafox/framework';
import { put, takeLatest } from 'redux-saga/effects';
import { APP_GROUP, RESOURCE_GROUP } from '@metafox/group';

function* getMember({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);
  const tagType = item?.reg_method ? 'member' : 'other';

  const dataSource = yield* getResourceAction(
    APP_GROUP,
    RESOURCE_GROUP,
    'getForTagMembersInFeed'
  );

  yield put({
    type: 'group/saga/pickerMember/response',
    payload: {
      dataSource,
      placeHolderSearch:
        tagType === 'member' ? 'search_for_members' : 'search_for_friends'
    }
  });
}

export default takeLatest('group/saga/pickerMember/get', getMember);
