/**
 * @type: saga
 * name: friend.saga.friendPicker
 */
import {
  getGlobalContext,
  ItemLocalAction,
  getItem,
  getResourceAction
} from '@metafox/framework';
import { SimpleUserShape } from '@metafox/ui/types';
import { takeEvery, put, take } from 'redux-saga/effects';
import { APP_FRIEND, RESOURCE_FRIEND } from '@metafox/friend';

export function* friendPicker({
  payload,
  meta
}: ItemLocalAction<
  {
    users: Array<SimpleUserShape>;
    parentIdentity: string;
    tagType: string;
    parentType?: string;
    userIdentity?: string;
    forceUpdateValueOnClose?: boolean;
  },
  { onSuccess: (value) => void }
>) {
  const { dialogBackend, compactData } = yield* getGlobalContext();
  const {
    users,
    parentIdentity,
    parentType,
    forceUpdateValueOnClose,
    userIdentity
  } = payload;
  const parentUser = yield* getItem(parentIdentity);
  const user = yield* getItem(userIdentity);
  let initialParams = {};
  let placeholder = 'search_for_friends';
  let dataSource = yield* getResourceAction(
    APP_FRIEND,
    RESOURCE_FRIEND,
    'getForMentionFriends'
  );

  if (parentType && ['page', 'group'].includes(parentType)) {
    yield put({
      type: `${parentType}/saga/pickerMember/get`,
      payload: { identity: parentIdentity, userIdentity }
    });

    const result = yield take(`${parentType}/saga/pickerMember/response`);
    const { dataSource: config, placeHolderSearch } = result?.payload || {};

    if (config) {
      dataSource = config;
    }

    if (placeHolderSearch) {
      placeholder = placeHolderSearch;
    }

    initialParams = compactData(dataSource?.apiParams, {
      owner_id: parentUser?.id,
      user_id: user?.id
    });
  } else {
    initialParams = compactData(dataSource?.apiParams, {});
  }

  const { onSuccess } = meta;
  const value = yield dialogBackend.present({
    component: 'friend.dialog.MultipleFriendPicker',
    props: {
      forceUpdateValueOnClose,
      apiUrl: dataSource?.apiUrl,
      value: users,
      initialParams,
      placeholder
    }
  });

  onSuccess && onSuccess(value);
}

const sagaEffect = [takeEvery('friend/friendPicker', friendPicker)];

export default sagaEffect;
