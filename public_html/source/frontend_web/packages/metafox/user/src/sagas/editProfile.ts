/**
 * @type: saga
 * name: user/editProfile
 */

import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { isEmpty, uniqueId } from 'lodash';
import { takeEvery } from 'redux-saga/effects';

function* editProfile(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { navigate } = yield* getGlobalContext();

  navigate(`/user/${item.id}/profile`);
}

function* updateProfileInfo() {
  const { getPageParams } = yield* getGlobalContext();
  const { appName, resourceName, id } = getPageParams();
  const identity = `${appName}.entities.${resourceName}.${id}`;
  const item = yield* getItem(identity);

  if (isEmpty(item)) return;

  yield* patchEntity(identity, {
    _updateProfileInfo: uniqueId('userProfile')
  });
}

const sagas = [
  takeEvery('user/editProfile', editProfile),
  takeEvery('user/updateProfileInfo', updateProfileInfo)
];

export default sagas;
